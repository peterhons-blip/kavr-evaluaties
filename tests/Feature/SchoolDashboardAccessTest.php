<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Support\SchoolRoleCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    private function school(string $code): School
    {
        $school = School::create([
            'name' => 'Testacademie '.$code,
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

    public function test_school_administrator_can_open_dashboard_students_staff_and_roles(): void
    {
        $school = $this->school('A');
        $user = $this->member($school, 'school_admin');
        $school->students()->create([
            'dko3_stamnummer' => '000001234',
            'first_name' => 'Fictief',
            'last_name' => 'Testkind',
            'is_active' => true,
        ]);

        $this->actingAs($user)->withSession(['active_school_id' => $school->id])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Actieve leerlingen')
            ->assertSee('Leerlingen')
            ->assertSee('Personeelsleden')
            ->assertSee('Rollen');
        $this->get(route('school.students.index'))->assertOk();
        $this->get(route('school.members.index'))->assertOk();
        $this->get(route('school.roles.index'))->assertOk();
    }

    public function test_teacher_can_open_school_dashboard_without_management_links(): void
    {
        $school = $this->school('B');
        $teacher = $this->member($school, 'teacher');
        $this->actingAs($teacher)->withSession(['active_school_id' => $school->id])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Leerlingen')
            ->assertDontSee('Personeelsleden beheren')
            ->assertDontSee('Rollen en rechten');
        $this->get(route('school.students.index'))->assertOk();
        $this->get(route('school.students.create'))->assertForbidden();
        $this->get(route('school.members.index'))->assertForbidden();
        $this->get(route('school.roles.index'))->assertForbidden();
    }

    public function test_student_counter_is_scoped_to_active_school(): void
    {
        $a = $this->school('C');
        $b = $this->school('D');
        $manager = $this->member($a, 'school_admin');
        $a->students()->create([
            'dko3_stamnummer' => '1', 'first_name' => 'Binnen',
            'last_name' => 'School', 'is_active' => true,
        ]);
        foreach (range(1, 3) as $number) {
            $b->students()->create([
                'dko3_stamnummer' => (string) $number,
                'first_name' => 'Andere',
                'last_name' => 'School',
                'is_active' => true,
            ]);
        }
        $this->actingAs($manager)->withSession(['active_school_id' => $a->id])
            ->get(route('dashboard'))->assertOk()
            ->assertSee('Testacademie C')
            ->assertDontSee('Testacademie D');
        $this->assertSame(1, $a->students()->count());
        $this->assertSame(3, $b->students()->count());
    }

    public function test_inactive_school_membership_cannot_open_dashboard(): void
    {
        $school = $this->school('E');
        $user = $this->member($school, 'teacher');
        $user->schools()->updateExistingPivot($school->id, ['is_active' => false]);
        $this->actingAs($user)->withSession(['active_school_id' => $school->id])
            ->get(route('dashboard'))->assertRedirect(route('schools.select'));
    }
}
