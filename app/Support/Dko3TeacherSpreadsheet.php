<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;
use ZipArchive;
use SimpleXMLElement;

final class Dko3TeacherSpreadsheet
{
    /**
     * Lees de eerste werkbladexport uit Excel (XLSX) of een CSV.
     * Geen extern Composer-package nodig. Formules worden nooit uitgevoerd.
     */
    public static function read(string $path, string $extension): array
    {
        $rows = match (strtolower($extension)) {
            'csv' => self::csv($path),
            'xlsx' => self::xlsx($path),
            default => throw ValidationException::withMessages(['file' => 'Enkel XLSX- en CSV-bestanden zijn toegestaan.']),
        };
        return self::normalize($rows);
    }

    private static function csv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) throw ValidationException::withMessages(['file' => 'Kan het CSV-bestand niet lezen.']);
        try {
            $first = fgets($handle);
            rewind($handle);
            $separator = substr_count((string) $first, ';') >= substr_count((string) $first, ',') ? ';' : ',';
            $rows = [];
            while (($row = fgetcsv($handle, 0, $separator, '"', '\\')) !== false) {
                $rows[] = array_map(static fn ($value) => is_string($value) ? trim(mb_convert_encoding($value, 'UTF-8', 'UTF-8,Windows-1252,ISO-8859-1')) : '', $row);
                if (count($rows) > 20001) throw ValidationException::withMessages(['file' => 'Maximaal 20.000 rijen per import.']);
            }
            return $rows;
        } finally {
            fclose($handle);
        }
    }

    private static function xlsx(string $path): array
    {
        if (! class_exists(ZipArchive::class) || ! class_exists(SimpleXMLElement::class)) {
            throw ValidationException::withMessages(['file' => 'De PHP-extensies zip en SimpleXML moeten ingeschakeld zijn.']);
        }
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) throw ValidationException::withMessages(['file' => 'Kan het XLSX-bestand niet openen.']);
        try {
            $shared = [];
            $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedXml !== false) {
                $xml = self::xml($sharedXml);
                foreach ($xml->children($xml->getNamespaces(true)[''] ?? null)->si as $si) {
                    $parts = [];
                    foreach ($si->xpath('.//*[local-name()="t"]') ?: [] as $t) $parts[] = (string) $t;
                    $shared[] = implode('', $parts);
                }
            }
            $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($sheet === false || strlen($sheet) > 16 * 1024 * 1024) {
                throw ValidationException::withMessages(['file' => 'Het eerste Excel-werkblad ontbreekt of is te groot.']);
            }
            $xml = self::xml($sheet);
            $rows = [];
            foreach ($xml->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') ?: [] as $row) {
                $values = [];
                foreach ($row->xpath('./*[local-name()="c"]') ?: [] as $cell) {
                    preg_match('/^[A-Z]+/', (string) $cell['r'], $match);
                    if (!$match) continue;
                    $column = 0;
                    foreach (str_split($match[0]) as $letter) $column = $column * 26 + ord($letter) - 64;
                    if ($column > 200) continue;
                    $type = (string) $cell['t'];
                    $node = ($cell->xpath('./*[local-name()="v"]') ?: [])[0] ?? null;
                    $inline = ($cell->xpath('.//*[local-name()="is"]//*[local-name()="t"]') ?: []);
                    $raw = $type === 'inlineStr'
                        ? implode('', array_map('strval', $inline))
                        : ($node === null ? '' : (string) $node);
                    $values[$column - 1] = $type === 's' ? ($shared[(int) $raw] ?? '') : $raw;
                }
                if ($values) {
                    $width = min(200, max(array_keys($values)) + 1);
                    $rows[] = array_replace(array_fill(0, $width, ''), $values);
                }
                if (count($rows) > 20001) throw ValidationException::withMessages(['file' => 'Maximaal 20.000 rijen per import.']);
            }
            return $rows;
        } finally {
            $zip->close();
        }
    }

    private static function xml(string $source): SimpleXMLElement
    {
        $xml = simplexml_load_string($source, SimpleXMLElement::class, LIBXML_NONET | LIBXML_COMPACT);
        if (!$xml) throw ValidationException::withMessages(['file' => 'Ongeldige Excel XML-inhoud.']);
        return $xml;
    }

    private static function key(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['é','è','ë','ê','ï','ö','ü','-','_','/','.'], ['e','e','e','e','i','o','u',' ',' ',' ',' '], $value);
        return preg_replace('/[^a-z0-9]/', '', $value);
    }

    /**
     * Selecteer alleen een transactioneel adres als de naam ondubbelzinnig
     * overeenkomt. Bij meerdere kandidaten zonder duidelijke match is
     * handmatige correctie veiliger dan aan de verkeerde persoon koppelen.
     */
    private static function chooseTransactionalEmail(array $candidates, string $first, string $last): string
    {
        if (count($candidates) === 1) {
            return $candidates[0];
        }

        $firstKey = self::key($first);
        $lastKey = self::key($last);
        if ($firstKey === '' || $lastKey === '') {
            return '';
        }

        $scored = [];
        foreach ($candidates as $candidate) {
            $local = explode('@', $candidate, 2)[0];
            $localKey = self::key($local);
            $both = str_contains($localKey, $firstKey) && str_contains($localKey, $lastKey);
            $score = $both ? 2 : 0;
            $scored[] = ['email' => $candidate, 'score' => $score];
        }

        $matches = array_values(array_filter($scored, static fn (array $item) => $item['score'] === 2));
        return count($matches) === 1 ? $matches[0]['email'] : '';
    }

    private static function normalize(array $rows): array
    {
        $aliases = [
            'full_name' => ['naam', 'volledige naam'],
            'first_name' => ['voornaam', 'firstname', 'first name', 'roepnaam'],
            'last_name' => ['familienaam', 'achternaam', 'lastname', 'last name'],
            'email' => ['e-mailadres account', 'email account', 'email', 'e mail', 'emailadres', 'e mailadres', 'mailadres', 'mail'],
            'transactional_emails' => [
                'e-mailadressen transactioneel (gescheiden door komma)',
                'e-mailadressen transactioneel (gescheiden door puntkomma)',
                'e-mailadressen transactioneel',
            ],
            'phone' => ['telefoonnummers', 'telefoonnummer', 'telefoon'],
            'dko3_roles' => ['rol(len)', 'rollen'],
            'dko3_active_assignments' => ['actieve opdrachten'],
            'dko3_subject_groups' => ['vakgroepen'],
            'dko3_locations' => ['vestigingsplaatsen'],
            'dko3_subjects' => ['vakken (huidig schooljaar)', 'vakken'],
        ];

        $headers = null;
        $headerLine = null;
        foreach (array_slice($rows, 0, 20, true) as $i => $row) {
            $keys = array_map(fn ($v) => self::key((string) $v), $row);
            $mapping = [];
            foreach ($aliases as $field => $names) {
                foreach ($keys as $column => $key) {
                    if (in_array($key, array_map([self::class, 'key'], $names), true)) {
                        $mapping[$field] = $column;
                        break;
                    }
                }
            }
            if ((isset($mapping['email']) || isset($mapping['transactional_emails'])) &&
                (isset($mapping['full_name']) ||
                    (isset($mapping['first_name']) && isset($mapping['last_name'])))) {
                $headers = $mapping;
                $headerLine = $i;
                break;
            }
        }

        if ($headers === null) {
            throw ValidationException::withMessages([
                'file' => 'Kolommen naam (Achternaam, Voornaam) of voornaam en familienaam, plus account- of transactioneel e-mailadres, niet gevonden.',
            ]);
        }

        $out = [];
        $errors = [];
        $seen = [];
        foreach (array_slice($rows, $headerLine + 1, null, true) as $i => $row) {
            $value = static fn (string $field) => isset($headers[$field])
                ? trim((string) ($row[$headers[$field]] ?? ''))
                : '';
            $first = $value('first_name');
            $last = $value('last_name');
            $full = $value('full_name');

            if (($first === '' || $last === '') && $full !== '') {
                // DKO3 exporteert 'Familienaam, Voornaam'. Zonder komma
                // raden we de splitsing niet: vermijd onjuiste persoonsdata.
                $parts = explode(',', $full, 2);
                if (count($parts) === 2) {
                    $last = trim($parts[0]);
                    $first = trim($parts[1]);
                }
            }

            $email = mb_strtolower($value('email'));
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $transactional = $value('transactional_emails');
                $candidates = array_values(array_unique(array_filter(
                    array_map(
                        static fn (string $candidate) => mb_strtolower(trim($candidate)),
                        preg_split('/[,;\\s]+/', $transactional) ?: []
                    ),
                    static fn (string $candidate) => (bool) filter_var($candidate, FILTER_VALIDATE_EMAIL)
                )));
                $email = self::chooseTransactionalEmail($candidates, $first, $last);
            }
            if ($first === '' && $last === '' && $email === '') {
                continue;
            }
            if ($first === '' || $last === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Rij '.($i + 1).': naam of geldig e-mailadres ontbreekt; controleer accountadres en transactionele adressen (bij meerdere adressen is een duidelijke naamsmatch vereist).';
                continue;
            }
            if (isset($seen[$email])) {
                $errors[] = 'Rij '.($i + 1).': dubbel account-e-mailadres '.$email.'.';
                continue;
            }

            $seen[$email] = true;
            $out[] = [
                'first_name' => mb_substr($first, 0, 100),
                'last_name' => mb_substr($last, 0, 100),
                'email' => $email,
                'phone' => mb_substr($value('phone'), 0, 60),
                'dko3_roles' => mb_substr($value('dko3_roles'), 0, 10000),
                'dko3_active_assignments' => mb_substr($value('dko3_active_assignments'), 0, 10000),
                'dko3_subject_groups' => mb_substr($value('dko3_subject_groups'), 0, 10000),
                'dko3_locations' => mb_substr($value('dko3_locations'), 0, 10000),
                'dko3_subjects' => mb_substr($value('dko3_subjects'), 0, 10000),
            ];
        }
        if (! count($out)) {
            $errors[] = 'Geen geldige leerkrachten gevonden.';
        }

        return ['rows' => $out, 'errors' => array_slice($errors, 0, 60), 'total_errors' => count($errors)];
    }
}
