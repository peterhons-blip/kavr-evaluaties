<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

final class Dko3EducationSpreadsheet
{
    public static function read(string $path, string $extension, string $type): array
    {
        $aliases = $type === 'domains'
            ? ['stamnummer' => 'number', 'domein' => 'domain']
            : ['leerling' => 'student', 'vak' => 'subject', 'graad' => 'grade',
               'vestigingsplaats' => 'location', 'leerkracht' => 'teacher'];
        $raw = Dko3StudentSpreadsheet::rawRows($path, $extension);
        $headers = null;
        $headerRow = 0;
        foreach (array_slice($raw, 0, 20, true) as $index => $row) {
            $found = [];
            foreach ($row as $col => $value) {
                $key = preg_replace('/[^a-z]/u', '', mb_strtolower(trim((string) $value)));
                if (isset($aliases[$key])) $found[$aliases[$key]] = $col;
            }
            if (count($found) === count($aliases)) {
                $headers = $found;
                $headerRow = $index;
                break;
            }
        }
        if ($headers === null) {
            throw ValidationException::withMessages([
                'file' => 'De noodzakelijke DKO3-kolommen ontbreken: '.implode(', ', array_keys($aliases)).'.',
            ]);
        }
        $rows = [];
        $errors = [];
        $seen = [];
        foreach (array_slice($raw, $headerRow + 1, null, true) as $index => $values) {
            $item = [];
            foreach ($headers as $field => $col) $item[$field] = trim((string) ($values[$col] ?? ''));
            if (count(array_filter($item, static fn ($v) => $v !== '')) === 0) continue;
            if (in_array('', $item, true) || max(array_map('mb_strlen', $item)) > 200) {
                $errors[] = 'Rij '.($index + 1).': onvolledige of ongeldige gegevens.';
                continue;
            }
            if ($type === 'domains') {
                if (! preg_match('/^[0-9]{1,32}$/D', $item['number']) ||
                    ! preg_match('/^(.+?)\\s*\\(([A-Za-z]{2,8})\\)$/u', $item['domain'], $matches)) {
                    $errors[] = 'Rij '.($index + 1).': ongeldig stamnummer of domein (verwacht: Muziek (Mu)).';
                    continue;
                }
                $item['domain_name'] = trim($matches[1]);
                $item['domain_code'] = $matches[2];
                $key = $item['number'].'|'.mb_strtolower($item['domain_code']);
            } else {
                if (! preg_match('/^(.*?),\\s*(.+)$/u', $item['student'])) {
                    $errors[] = 'Rij '.($index + 1).': leerlingnaam moet Familienaam, Voornaam zijn.';
                    continue;
                }
                $key = implode('|', array_map(static fn ($v) => mb_strtolower(trim($v)),
                    [$item['student'], $item['subject'], $item['grade'], $item['location'], $item['teacher']]));
            }
            if (isset($seen[$key])) {
                // Identieke exportregels zijn geen afzonderlijke inschrijvingen.
                continue;
            }
            $seen[$key] = true;
            $item['line'] = $index + 1;
            $rows[] = $item;
        }
        if (! $rows) $errors[] = 'Geen geldige gegevens gevonden.';
        return ['rows' => $rows, 'errors' => array_slice($errors, 0, 30),
                'total_errors' => count($errors)];
    }
}
