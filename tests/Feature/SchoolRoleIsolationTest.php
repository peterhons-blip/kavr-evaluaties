<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Support\SchoolAccess;
use App\Support\SchoolRoleCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolRoleIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function school(string $code): School
    {
        $school = School::create([
            'name' => 'Testschool '.$code,
            'code' => $code,
            'slug' => strtolower($code),
            'country_code' => 'BE',
            'is_active' => true,
        ]);
        SchoolRoleCatalog::ensureFor($school);
        return $school;
    }

    private function user(): User
    {
        return User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'Gebruiker',
            'is_active' => true,
        ]);
    }

    public function test_a_teacher_cannot_manage_school_users(): void
    {
        $school = $this->school('A');
        $user = $this->user();
        $school->users()->attach($user->id, ['role' => 'teacher', 'is_active' => true]);

        $this->assertFalse(SchoolAccess::allows($user, $school, 'users.manage'));
        $this->assertTrue(SchoolAccess::allows($user, $school, 'evaluations.write'));
    }

    public function test_school_role_permissions_do_not_leak_between_schools(): void
    {
        $a = $this->school('A');
        $b = $this->school('B');
        $user = $this->user();

        $a->users()->attach($user->id, ['role' => 'school_admin', 'is_active' => true]);
        $b->users()->attach($user->id, ['role' => 'teacher', 'is_active' => true]);

        $this->assertTrue(SchoolAccess::allows($user, $a, 'users.manage'));
        $this->assertFalse(SchoolAccess::allows($user, $b, 'users.manage'));

        $b->roles()->where('slug', 'teacher')->firstOrFail()->update([
            'permissions' => ['evaluations.view'],
        ]);

        $this->assertTrue(SchoolAccess::allows($user, $a, 'users.manage'));
        $this->assertFalse(SchoolAccess::allows($user, $b, 'evaluations.write'));
    }

    public function test_inactive_memberships_and_schools_have_no_permissions(): void
    {
        $school = $this->school('A');
        $user = $this->user();
        $school->users()->attach($user->id, ['role' => 'school_admin', 'is_active' => false]);
        $this->assertFalse(SchoolAccess::allows($user, $school, 'users.manage'));

        $school->users()->updateExistingPivot($user->id, ['is_active' => true]);
        $school->update(['is_active' => false]);
        $this->assertFalse(SchoolAccess::allows($user, $school, 'users.manage'));
    }

    public function test_non_platform_admin_cannot_open_global_user_administration(): void
    {
        $this->actingAs($this->user())
            ->get(route('platform.users.index'))
            ->assertForbidden();
    }

    public function test_regular_school_admin_cannot_manage_another_school(): void
    {
        $a = $this->school('A');
        $b = $this->school('B');
        $user = $this->user();
        $a->users()->attach($user->id, ['role' => 'school_admin', 'is_active' => true]);

        $this->actingAs($user)
            ->withSession(['active_school_id' => $b->id])
            ->get(route('school.members.index'))
            ->assertRedirect(route('schools.select'));
    }
}
