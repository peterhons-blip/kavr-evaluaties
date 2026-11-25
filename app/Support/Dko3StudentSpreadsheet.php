<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;
use ZipArchive;

final class Dko3StudentSpreadsheet
{
    /**
     * DKO3: eerste werkblad met kolommen stamnummer, naam, voornaam.
     * Bewaar stamnummer als tekst zodat voorloopnullen intact blijven.
     * Geen persoonsgegevens vastleggen buiten de importpreview.
     */
    public static function read(string $path, string $extension): array
    {
        $raw = strtolower($extension) === 'csv' ? self::csv($path) : self::xlsx($path);
        $headers = null;
        $headerIndex = 0;
        foreach (array_slice($raw, 0, 20, true) as $index => $row) {
            $map = [];
            foreach ($row as $col => $value) {
                $key = preg_replace('/[^a-z]/', '', mb_strtolower(trim((string) $value)));
                foreach (['stamnummer' => 'dko3_stamnummer', 'naam' => 'last_name', 'familienaam' => 'last_name', 'voornaam' => 'first_name'] as $alias => $field) {
                    if ($key === $alias) $map[$field] = $col;
                }
            }
            if (count($map) === 3) { $headers = $map; $headerIndex = $index; break; }
        }
        if ($headers === null) throw ValidationException::withMessages([
            'file' => 'DKO3-kolommen stamnummer, naam en voornaam niet gevonden.',
        ]);
        $rows = [];
        $errors = [];
        $seen = [];
        foreach (array_slice($raw, $headerIndex + 1, null, true) as $index => $line) {
            $field = static fn (string $key): string => trim((string) ($line[$headers[$key]] ?? ''));
            $id = $field('dko3_stamnummer');
            $first = $field('first_name');
            $last = $field('last_name');
            if ($id === '' && $first === '' && $last === '') continue;
            if ($id === '' || $first === '' || $last === '' ||
                mb_strlen($id) > 32 || mb_strlen($first) > 150 || mb_strlen($last) > 150 ||
                ! preg_match('/^[0-9]+$/D', $id)) {
                $errors[] = 'Rij '.($index + 1).': ongeldig stamnummer, voornaam of familienaam.';
                continue;
            }
            if (isset($seen[$id])) {
                $errors[] = 'Rij '.($index + 1).': dubbel stamnummer in de export.';
                continue;
            }
            $seen[$id] = true;
            $rows[] = ['dko3_stamnummer' => $id, 'first_name' => $first, 'last_name' => $last];
        }
        if (count($rows) === 0) $errors[] = 'Geen leerlingen gevonden.';
        return ['rows' => $rows, 'errors' => array_slice($errors, 0, 30), 'total_errors' => count($errors)];
    }

    /**
     * Hergebruik dezelfde veilige XLSX/CSV-lezer voor de domein- en vakexports.
     * De gespecialiseerde importservice controleert vervolgens de kolommen.
     */
    public static function rawRows(string $path, string $extension): array
    {
        return strtolower($extension) === 'csv' ? self::csv($path) : self::xlsx($path);
    }

    private static function csv(string $path): array
    {
        $file = fopen($path, 'rb');
        if ($file === false) throw ValidationException::withMessages(['file' => 'Kan CSV niet lezen.']);
        try {
            $first = fgets($file);
            rewind($file);
            $separator = substr_count((string) $first, ';') >= substr_count((string) $first, ',') ? ';' : ',';
            $rows = [];
            while (($line = fgetcsv($file, 0, $separator, '"', '\\')) !== false) {
                $rows[] = array_map(static fn ($v): string => trim(mb_convert_encoding((string) $v, 'UTF-8', 'UTF-8,Windows-1252,ISO-8859-1')), $line);
                if (count($rows) > 20001) throw ValidationException::withMessages(['file' => 'Maximaal 20.000 regels per import.']);
            }
            return $rows;
        } finally { fclose($file); }
    }

    private static function xlsx(string $path): array
    {
        if (! class_exists(ZipArchive::class) || ! class_exists(\SimpleXMLElement::class)) {
            throw ValidationException::withMessages(['file' => 'PHP-extensies zip en SimpleXML zijn vereist.']);
        }
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) throw ValidationException::withMessages(['file' => 'Ongeldig XLSX-bestand.']);
        try {
            $shared = [];
            $sharedData = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedData !== false) {
                if (strlen($sharedData) > 8 * 1024 * 1024) throw ValidationException::withMessages(['file' => 'Excel-teksten zijn te groot.']);
                $xml = self::xml($sharedData);
                foreach ($xml->xpath('//*[local-name()="si"]') ?: [] as $item) {
                    $parts = [];
                    foreach ($item->xpath('.//*[local-name()="t"]') ?: [] as $t) $parts[] = (string) $t;
                    $shared[] = implode('', $parts);
                }
            }
            $data = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($data === false || strlen($data) > 16 * 1024 * 1024) throw ValidationException::withMessages(['file' => 'Eerste Excel-werkblad ontbreekt of is te groot.']);
            $xml = self::xml($data);
            $rows = [];
            foreach ($xml->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') ?: [] as $row) {
                $cells = [];
                foreach ($row->xpath('./*[local-name()="c"]') ?: [] as $cell) {
                    preg_match('/^[A-Z]+/', (string) $cell['r'], $match);
                    if (!$match) continue;
                    $column = 0;
                    foreach (str_split($match[0]) as $char) $column = $column * 26 + ord($char) - 64;
                    if ($column > 50) continue;
                    $type = (string) $cell['t'];
                    $v = ($cell->xpath('./*[local-name()="v"]') ?: [])[0] ?? null;
                    $inline = $cell->xpath('.//*[local-name()="is"]//*[local-name()="t"]') ?: [];
                    $value = $type === 'inlineStr' ? implode('', array_map('strval', $inline)) : ($v === null ? '' : (string) $v);
                    $cells[$column - 1] = $type === 's' ? ($shared[(int) $value] ?? '') : $value;
                }
                if ($cells) $rows[] = array_replace(array_fill(0, max(array_keys($cells)) + 1, ''), $cells);
                if (count($rows) > 20001) throw ValidationException::withMessages(['file' => 'Maximaal 20.000 regels per import.']);
            }
            return $rows;
        } finally { $zip->close(); }
    }

    private static function xml(string $source): \SimpleXMLElement
    {
        $xml = simplexml_load_string($source, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_COMPACT);
        if ($xml === false) throw ValidationException::withMessages(['file' => 'Ongeldige Excel-XML.']);
        return $xml;
    }
}
