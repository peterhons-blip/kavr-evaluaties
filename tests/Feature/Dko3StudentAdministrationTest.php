<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Support\Dko3StudentSpreadsheet;
use App\Support\SchoolRoleCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class Dko3StudentAdministrationTest extends TestCase
{
    use RefreshDatabase;

    private function academy(string $code): School
    {
        $school = School::create([
            'name' => 'Academie '.$code,
            'code' => $code,
            'slug' => strtolower($code),
            'country_code' => 'BE',
            'is_active' => true,
        ]);
        SchoolRoleCatalog::ensureFor($school);
        return $school;
    }

    private function member(School $school, string $role): User
    {
        $user = User::factory()->create(['is_active' => true, 'is_platform_admin' => false]);
        $user->schools()->attach($school->id, ['role' => $role, 'is_active' => true]);
        return $user;
    }

    private function sampleCsv(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('alleleerlingen.csv',
            "stamnummer;naam;voornaam\n000123001;Voorbeeld;Alex\n000123002;Voorbeeld;Robin\n");
    }

    public function test_school_manager_can_preview_and_commit_csv_without_deactivating_missing_students(): void
    {
        Storage::fake('local');
        $school = $this->academy('A');
        $admin = $this->member($school, 'school_admin');
        $other = Student::create([
            'school_id' => $school->id,
            'dko3_stamnummer' => '000000001',
            'first_name' => 'Bestaand',
            'last_name' => 'Kind',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->withSession(['active_school_id' => $school->id])
            ->post(route('school.students.preview'), ['file' => $this->sampleCsv()])
            ->assertOk()->assertSee('2')->assertSee('000123001');

        $token = session('student_import_token');
        $this->assertNotEmpty($token);

        $this->post(route('school.students.commit'), ['token' => $token, 'confirm' => '1'])
            ->assertRedirect(route('school.students.index'));

        $this->assertDatabaseHas('students', [
            'school_id' => $school->id, 'dko3_stamnummer' => '000123001',
            'first_name' => 'Alex', 'last_name' => 'Voorbeeld',
        ]);
        $this->assertTrue($other->fresh()->is_active);
        $this->assertSame(3, Student::where('school_id', $school->id)->count());

        // Re-import does not create duplicates.
        $this->post(route('school.students.preview'), ['file' => $this->sampleCsv()])->assertOk();
        $this->post(route('school.students.commit'), [
            'token' => session('student_import_token'), 'confirm' => '1',
        ])->assertRedirect();
        $this->assertSame(3, Student::where('school_id', $school->id)->count());
    }

    public function test_teacher_without_students_manage_cannot_import_or_write(): void
    {
        $school = $this->academy('A');
        $teacher = $this->member($school, 'teacher');
        $this->actingAs($teacher)->withSession(['active_school_id' => $school->id]);
        $this->get(route('school.students.index'))->assertOk();
        $this->get(route('school.students.import'))->assertForbidden();
        $this->post(route('school.students.preview'), ['file' => $this->sampleCsv()])->assertForbidden();
        $this->post(route('school.students.store'), [
            'dko3_stamnummer' => '00111', 'first_name' => 'Test',
            'last_name' => 'Leerling', 'is_active' => '1',
        ])->assertForbidden();
    }

    public function test_student_from_other_school_cannot_be_edited_through_id(): void
    {
        $a = $this->academy('A');
        $b = $this->academy('B');
        $manager = $this->member($a, 'school_admin');
        $other = Student::create([
            'school_id' => $b->id, 'dko3_stamnummer' => '111',
            'first_name' => 'Andere', 'last_name' => 'Academie',
        ]);
        $this->actingAs($manager)->withSession(['active_school_id' => $a->id]);
        $this->get(route('school.students.edit', $other))->assertNotFound();
        $this->put(route('school.students.update', $other), [
            'dko3_stamnummer' => '111', 'first_name' => 'Gewijzigd',
            'last_name' => 'Academie', 'is_active' => '1',
        ])->assertNotFound();
        $this->assertSame('Andere', $other->fresh()->first_name);
    }

    public function test_preview_does_not_write_when_duplicate_stamnummer_is_present(): void
    {
        $school = $this->academy('A');
        $user = $this->member($school, 'school_admin');
        $file = UploadedFile::fake()->createWithContent('dubbel.csv',
            "stamnummer;naam;voornaam\n0001;Eerste;Jan\n0001;Tweede;Jan\n");
        $this->actingAs($user)->withSession(['active_school_id' => $school->id])
            ->post(route('school.students.preview'), ['file' => $file])
            ->assertRedirect()->assertSessionHasErrors('file');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_xlsx_parser_reads_dko3_columns_and_preserves_leading_zeroes(): void
    {
        if (!class_exists(ZipArchive::class)) $this->markTestSkipped('ZIP-extensie vereist.');
        $path = tempnam(sys_get_temp_dir(), 'dko3-').'.xlsx';
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true);
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0"?>'.
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.
            '<row r="1"><c r="A1" t="inlineStr"><is><t>stamnummer</t></is></c>'.
            '<c r="B1" t="inlineStr"><is><t>naam</t></is></c>'.
            '<c r="C1" t="inlineStr"><is><t>voornaam</t></is></c></row>'.
            '<row r="2"><c r="A2" t="inlineStr"><is><t>000123001</t></is></c>'.
            '<c r="B2" t="inlineStr"><is><t>Voorbeeld</t></is></c>'.
            '<c r="C2" t="inlineStr"><is><t>Leerling</t></is></c></row>'.
            '</sheetData></worksheet>');
        $zip->close();
        try {
            $result = Dko3StudentSpreadsheet::read($path, 'xlsx');
            $this->assertSame(0, $result['total_errors']);
            $this->assertSame('000123001', $result['rows'][0]['dko3_stamnummer']);
            $this->assertSame('Leerling', $result['rows'][0]['first_name']);
        } finally {
            @unlink($path);
        }
    }
}
