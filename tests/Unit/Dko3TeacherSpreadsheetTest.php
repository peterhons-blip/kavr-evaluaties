<?php

namespace Tests\Unit;

use App\Support\Dko3TeacherSpreadsheet;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class Dko3TeacherSpreadsheetTest extends TestCase
{
    public function test_it_reads_an_xlsx_with_shared_strings(): void
    {
        if (! class_exists(ZipArchive::class)) {
            $this->markTestSkipped('ZIP extension unavailable.');
        }

        $path = tempnam(sys_get_temp_dir(), 'dko3');
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true);

        $zip->addFromString('xl/sharedStrings.xml',
            '<?xml version="1.0" encoding="UTF-8"?>'.
            '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'.
            '<si><t>Voornaam</t></si><si><t>Familienaam</t></si><si><t>E-mailadres</t></si>'.
            '<si><t>Anna</t></si><si><t>Janssens</t></si><si><t>Anna@example.test</t></si>'.
            '</sst>'
        );
        $zip->addFromString('xl/worksheets/sheet1.xml',
            '<?xml version="1.0" encoding="UTF-8"?>'.
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'.
            '<sheetData>'.
            '<row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c><c r="C1" t="s"><v>2</v></c></row>'.
            '<row r="2"><c r="A2" t="s"><v>3</v></c><c r="B2" t="s"><v>4</v></c><c r="C2" t="s"><v>5</v></c></row>'.
            '</sheetData></worksheet>'
        );
        $zip->close();

        try {
            $parsed = Dko3TeacherSpreadsheet::read($path, 'xlsx');
            $this->assertSame(0, $parsed['total_errors']);
            $this->assertSame([
                'first_name' => 'Anna',
                'last_name' => 'Janssens',
                'email' => 'anna@example.test',
            ], $parsed['rows'][0]);
        } finally {
            @unlink($path);
        }
    }
}
