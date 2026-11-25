<?php

namespace Tests\Feature;

use App\Models\EducationDomain;
use App\Models\EducationProgram;
use App\Models\EducationGradeYear;
use App\Models\EducationSubject;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Support\SchoolRoleCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Dko3EducationStructureAndImportTest extends TestCase
{
    use RefreshDatabase;

    private function school(string $code): School
    {
        $school=School::create([
            'name'=>'Academie '.$code, 'code'=>$code,'slug'=>strtolower($code),
            'country_code'=>'BE','is_active'=>true,
        ]);
        SchoolRoleCatalog::ensureFor($school);
        return $school;
    }

    private function manager(School $school): User
    {
        $user=User::factory()->create(['is_active'=>true,'is_platform_admin'=>false]);
        $user->schools()->attach($school->id,['role'=>'school_admin','is_active'=>true]);
        return $user;
    }

    public function test_domain_import_can_be_repeated_without_duplicates_or_removing_other_school_years(): void
    {
        Storage::fake('local');
        $school=$this->school('DKO-A');
        $admin=$this->manager($school);
        EducationDomain::create(['school_id'=>$school->id,'name'=>'Muziek','code'=>'Mu']);
        EducationDomain::create(['school_id'=>$school->id,'name'=>'Beeld','code'=>'Be']);
        $student=Student::create([
            'school_id'=>$school->id,'dko3_stamnummer'=>'002012330',
            'first_name'=>'Lina','last_name'=>'Ahzaz','is_active'=>true,
        ]);
        $this->actingAs($admin)->withSession(['active_school_id'=>$school->id]);

        $csv="stamnummer;naam;voornaam;domein;straatnaam\n"
            ."002012330;Ahzaz;Lina;Muziek (Mu);Ongebruikte straat\n"
            ."002012330;Ahzaz;Lina;Beeld (Be);Ongebruikte straat\n";
        $this->post(route('school.education.imports.preview'),[
            'type'=>'domains','school_year'=>'2026-2027',
            'file'=>UploadedFile::fake()->createWithContent('domeinen.csv',$csv),
        ])->assertOk()->assertSee('2026-2027');
        $this->post(route('school.education.imports.commit'),[
            'token'=>session('education_import_token'),'confirm'=>'1',
        ])->assertRedirect(route('school.education.imports'))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('student_domain_enrollments',2);
        $this->assertDatabaseHas('student_school_years',[
            'student_id'=>$student->id,'school_year'=>'2026-2027',
        ]);
        $this->post(route('school.education.imports.preview'),[
            'type'=>'domains','school_year'=>'2026-2027',
            'file'=>UploadedFile::fake()->createWithContent('domeinen.csv',$csv),
        ])->assertOk();
        $this->post(route('school.education.imports.commit'),[
            'token'=>session('education_import_token'),'confirm'=>'1',
        ])->assertRedirect(route('school.education.imports'))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('student_domain_enrollments',2);
    }

    public function test_unknown_subject_staff_or_student_stops_preview_safely(): void
    {
        Storage::fake('local');
        $school=$this->school('DKO-B');
        $admin=$this->manager($school);
        $this->actingAs($admin)->withSession(['active_school_id'=>$school->id]);
        $csv="leerling;vak;graad;vestigingsplaats;leerkracht\n"
            ."Nietgekend, Kind;Piano (klassiek);2.3;Voeren;Jans, Leerkracht\n";
        $this->post(route('school.education.imports.preview'),[
            'type'=>'subjects','school_year'=>'2026-2027',
            'file'=>UploadedFile::fake()->createWithContent('vakken.csv',$csv),
        ])->assertOk()->assertSee('Leerling niet gevonden')
            ->assertDontSee('Definitief importeren');
        $this->assertDatabaseCount('student_subject_enrollments',0);
    }

    public function test_academy_curriculum_can_connect_program_option_year_subject_and_school_year(): void
    {
        $school=$this->school('DKO-C');
        $admin=$this->manager($school);
        $domain=EducationDomain::create(['school_id'=>$school->id,'name'=>'Muziek','code'=>'Mu']);
        $subject=EducationSubject::create([
            'school_id'=>$school->id,'education_domain_id'=>$domain->id,
            'name'=>'Muziekleer','evaluation_type'=>'kcj',
        ]);
        $this->actingAs($admin)->withSession(['active_school_id'=>$school->id]);
        $this->post(route('school.education.programs.store'),[
            'education_domain_id'=>$domain->id,'name'=>'Muziekopleiding',
            'kind'=>'regular','audience'=>'youth',
        ])->assertRedirect(route('school.education.programs'))->assertSessionHasNoErrors();
        $program=EducationProgram::firstOrFail();
        $this->post(route('school.education.options.store'),[
            'education_program_id'=>$program->id,'name'=>'Klassiek',
        ])->assertSessionHasNoErrors();
        $option=$program->options()->firstOrFail();
        $this->post(route('school.education.years.store'),[
            'education_program_id'=>$program->id,
            'education_option_id'=>$option->id,
            'code'=>'2.3','grade'=>'2','study_year'=>'3',
        ])->assertSessionHasNoErrors();
        $year=EducationGradeYear::firstOrFail();
        $this->post(route('school.education.offerings.store'),[
            'education_grade_year_id'=>$year->id,'education_subject_id'=>$subject->id,
            'school_year'=>'2026-2027','hours_per_week'=>'2',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('education_subject_offerings',[
            'school_id'=>$school->id,'education_grade_year_id'=>$year->id,
            'education_subject_id'=>$subject->id,'school_year'=>'2026-2027',
        ]);
    }

    public function test_student_location_filter_is_scoped_to_own_academy(): void
    {
        $a=$this->school('LOC-A');
        $b=$this->school('LOC-B');
        $admin=$this->manager($a);
        $foreignLocation=\App\Models\SchoolLocation::create([
            'school_id'=>$b->id,'name'=>'Kanne','is_active'=>true,
        ]);
        $this->actingAs($admin)->withSession(['active_school_id'=>$a->id])
            ->get(route('school.students.index', ['location_id'=>$foreignLocation->id]))
            ->assertNotFound();
    }

    public function test_another_schools_program_cannot_be_changed(): void
    {
        $a=$this->school('SCOPE-A');
        $b=$this->school('SCOPE-B');
        $admin=$this->manager($a);
        $domain=EducationDomain::create(['school_id'=>$b->id,'name'=>'Woord','code'=>'Wo']);
        $program=EducationProgram::create([
            'school_id'=>$b->id,'education_domain_id'=>$domain->id,
            'name'=>'Theater','kind'=>'regular',
        ]);
        $this->actingAs($admin)->withSession(['active_school_id'=>$a->id])
            ->put(route('school.education.programs.update',$program),[
                'education_domain_id'=>$domain->id,'name'=>'Hacked','kind'=>'regular','is_active'=>'1',
            ])->assertNotFound();
        $this->assertSame('Theater',$program->fresh()->name);
    }
}
