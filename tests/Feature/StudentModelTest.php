<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\Student;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeSchool(string $code): School
    {
        return School::create([
            'name' => 'Testacademie ' . $code,
            'code' => $code,
            'slug' => strtolower($code),
            'country_code' => 'BE',
            'is_active' => true,
        ]);
    }

    public function test_dko3_stamnummer_keeps_leading_zeroes_and_is_scoped_per_school(): void
    {
        $schoolA = $this->makeSchool('AAA');
        $schoolB = $this->makeSchool('BBB');

        $studentA = Student::create([
            'school_id' => $schoolA->id,
            'dko3_stamnummer' => '000123001',
            'first_name' => 'Voornaam',
            'last_name' => 'Achternaam',
        ]);
        $studentB = Student::create([
            'school_id' => $schoolB->id,
            'dko3_stamnummer' => '000123001',
            'first_name' => 'Andere',
            'last_name' => 'Leerling',
        ]);

        $this->assertSame('000123001', $studentA->fresh()->dko3_stamnummer);
        $this->assertSame($schoolA->id, $studentA->school->id);
        $this->assertSame($schoolB->id, $studentB->school->id);
        $this->assertSame('Voornaam Achternaam', $studentA->full_name);
    }

    public function test_same_dko3_stamnummer_cannot_be_added_twice_within_one_school(): void
    {
        $school = $this->makeSchool('CCC');
        Student::create([
            'school_id' => $school->id,
            'dko3_stamnummer' => '000123456',
            'first_name' => 'Eerste',
            'last_name' => 'Leerling',
        ]);

        $this->expectException(QueryException::class);
        Student::create([
            'school_id' => $school->id,
            'dko3_stamnummer' => '000123456',
            'first_name' => 'Tweede',
            'last_name' => 'Leerling',
        ]);
    }
}
