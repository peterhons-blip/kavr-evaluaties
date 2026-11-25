<?php

namespace Tests\Feature;

use App\Models\EducationDomain;
use App\Models\EducationSubject;
use App\Models\School;
use App\Models\User;
use App\Support\SchoolRoleCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function school(string $code): School
    {
        $school = School::create([
            'name' => 'Academie '.$code, 'code' => $code,
            'slug' => strtolower($code), 'country_code' => 'BE',
            'is_active' => true,
        ]);
        SchoolRoleCatalog::ensureFor($school);
        return $school;
    }

    private function member(School $school, string $role): User
    {
        $user = User::factory()->create(['is_active'=>true,'is_platform_admin'=>false]);
        $user->schools()->attach($school->id,['role'=>$role,'is_active'=>true]);
        return $user;
    }

    public function test_school_admin_can_add_domain_and_subject_only_to_own_school(): void
    {
        $a = $this->school('ART-A');
        $b = $this->school('ART-B');
        $admin = $this->member($a,'school_admin');

        $this->actingAs($admin)->withSession(['active_school_id'=>$a->id]);
        $this->get(route('school.education.index'))->assertOk()->assertSee('Domeinen en vakken');

        $this->post(route('school.education.domains.store'), [
            'name'=>'Muziek', 'code'=>'MUZ',
        ])->assertRedirect(route('school.education.index'))->assertSessionHasNoErrors();
        $domain = EducationDomain::where('school_id',$a->id)->firstOrFail();

        $this->post(route('school.education.subjects.store'), [
            'education_domain_id'=>$domain->id,
            'name'=>'Instrument: klassiek', 'discipline'=>'Piano',
        ])->assertRedirect(route('school.education.index'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('education_subjects',[
            'school_id'=>$a->id, 'education_domain_id'=>$domain->id,
            'name'=>'Instrument: klassiek','discipline'=>'Piano',
        ]);
        $this->assertDatabaseHas('education_subjects', [
            'school_id' => $a->id, 'evaluation_type' => 'icj',
            'discipline' => 'Piano',
        ]);
        $this->post(route('school.education.subjects.store'), [
            'education_domain_id' => $domain->id, 'name' => 'Groepsmusiceren',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('education_subjects', [
            'school_id' => $a->id, 'name' => 'Groepsmusiceren',
            'evaluation_type' => 'kcj',
        ]);
        $this->assertDatabaseCount('education_domains', 1);
        $this->assertDatabaseMissing('education_domains', ['school_id'=>$b->id]);
    }

    public function test_teacher_cannot_change_catalog_and_cross_school_ids_are_rejected(): void
    {
        $a = $this->school('CAS-A');
        $b = $this->school('CAS-B');
        $admin = $this->member($a,'school_admin');
        $teacher = $this->member($a,'teacher');
        $foreign = EducationDomain::create(['school_id'=>$b->id,'name'=>'Dans','is_active'=>true]);

        $this->actingAs($teacher)->withSession(['active_school_id'=>$a->id])
            ->post(route('school.education.domains.store'), ['name'=>'Verboden'])
            ->assertForbidden();

        $this->actingAs($admin)->withSession(['active_school_id'=>$a->id])
            ->put(route('school.education.domains.update', $foreign), [
                'name'=>'Andere naam','is_active'=>'0',
            ])->assertNotFound();
        $this->post(route('school.education.subjects.store'), [
            'education_domain_id'=>$foreign->id,'name'=>'Niets',
        ])->assertNotFound();
        $this->assertSame('Dans', $foreign->fresh()->name);
    }

    public function test_platform_admin_must_select_school_and_cannot_mix_domains(): void
    {
        $a = $this->school('PL-A');
        $b = $this->school('PL-B');
        $admin = User::factory()->create(['is_active'=>true,'is_platform_admin'=>true]);
        $foreign = EducationDomain::create(['school_id'=>$b->id,'name'=>'Woordkunst-drama','is_active'=>true]);

        $this->actingAs($admin)->get(route('platform.education.index',['school_id'=>$a->id]))
            ->assertOk()->assertSee('Domeinen en vakken');
        $this->post(route('platform.education.subjects.store',['school_id'=>$a->id]), [
            'education_domain_id'=>$foreign->id,'name'=>'Voordracht',
        ])->assertNotFound();
        $this->assertDatabaseCount('education_subjects', 0);
    }
}
