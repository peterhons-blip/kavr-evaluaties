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

    private static function normalize(array $rows): array
    {
        $aliases = [
            'first_name' => ['voornaam','firstname','first name','naam voornaam','roepnaam'],
            'last_name' => ['familienaam','achternaam','lastname','last name','naam'],
            'email' => ['email','e mail','emailadres','e mailadres','mailadres','mail'],
        ];
        $headers = null; $headerLine = null;
        foreach (array_slice($rows, 0, 20, true) as $i => $row) {
            $keys = array_map(fn ($v) => self::key((string) $v), $row);
            $mapping = [];
            foreach ($aliases as $field => $names) {
                foreach ($keys as $column => $key) {
                    if (in_array($key, array_map([self::class,'key'], $names), true)) {
                        $mapping[$field] = $column; break;
                    }
                }
            }
            if (count($mapping) === 3) { $headers = $mapping; $headerLine = $i; break; }
        }
        if ($headers === null) {
            throw ValidationException::withMessages(['file' => 'Kolommen voornaam, familienaam en e-mailadres niet gevonden. Voeg het originele DKO3-bestand toe zodat we de kolommen kunnen afstemmen.']);
        }

        $out = []; $errors = []; $seen = [];
        foreach (array_slice($rows, $headerLine + 1, null, true) as $i => $row) {
            $first = trim((string) ($row[$headers['first_name']] ?? ''));
            $last = trim((string) ($row[$headers['last_name']] ?? ''));
            $email = mb_strtolower(trim((string) ($row[$headers['email']] ?? '')));
            if ($first === '' && $last === '' && $email === '') continue;
            if ($first === '' || $last === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Rij '.($i + 1).': voornaam, familienaam of e-mailadres ontbreekt of is ongeldig.';
                continue;
            }
            if (isset($seen[$email])) {
                $errors[] = 'Rij '.($i + 1).': dubbel e-mailadres '.$email.'.'; continue;
            }
            $seen[$email] = true;
            $out[] = ['first_name' => mb_substr($first,0,100), 'last_name' => mb_substr($last,0,100), 'email' => $email];
        }
        if (count($out) === 0) $errors[] = 'Geen geldige leerkrachten gevonden.';
        return ['rows' => $out, 'errors' => array_slice($errors, 0, 60), 'total_errors' => count($errors)];
    }
}
