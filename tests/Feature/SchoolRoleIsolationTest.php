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

    public function test_an_inactive_user_with_an_existing_session_is_denied(): void
    {
        $school = $this->school('A');
        $user = $this->user();
        $school->users()->attach($user->id, ['role' => 'teacher', 'is_active' => true]);
        $user->update(['is_active' => false]);

        $this->actingAs($user)
            ->withSession(['active_school_id' => $school->id])
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_school_admin_cannot_edit_another_schools_role(): void
    {
        $a = $this->school('A');
        $b = $this->school('B');
        $admin = $this->user();
        $a->users()->attach($admin->id, ['role' => 'school_admin', 'is_active' => true]);
        $otherRole = $b->roles()->where('slug', 'teacher')->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['active_school_id' => $a->id])
            ->get(route('school.roles.edit', $otherRole))
            ->assertNotFound();
    }

    public function test_school_admin_can_invite_a_user_without_platform_admin_rights(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $school = $this->school('A');
        $admin = $this->user();
        $school->users()->attach($admin->id, ['role' => 'school_admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->withSession(['active_school_id' => $school->id])
            ->post(route('school.members.store'), [
                'first_name' => 'Eva',
                'last_name' => 'Janssens',
                'email' => 'eva@example.test',
                'role' => 'teacher',
            ])
            ->assertSessionHasNoErrors();

        $created = User::where('email', 'eva@example.test')->firstOrFail();
        $this->assertFalse((bool) $created->is_platform_admin);
        $this->assertTrue($school->users()->where('users.id', $created->id)->exists());
        $this->assertFalse(SchoolAccess::allows($created, $school, 'users.manage'));
    }
}
