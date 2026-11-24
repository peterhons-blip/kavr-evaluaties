<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Support\SchoolAccess;
use App\Support\SchoolRoleCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PermissionsAndProfileTest extends TestCase
{
    use RefreshDatabase;

    private function school(string $code): School
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

    public function test_platform_admin_can_view_matrix_and_manage_user_specific_permissions(): void
    {
        $a = $this->school('A');
        $b = $this->school('B');
        $admin = User::factory()->create(['is_platform_admin' => true, 'is_active' => true]);
        $staff = User::factory()->create(['is_active' => true]);
        $staff->schools()->attach($a->id, ['role' => 'teacher', 'is_active' => true]);
        $staff->schools()->attach($b->id, ['role' => 'teacher', 'is_active' => true]);

        $this->actingAs($admin)
            ->get(route('platform.permissions.index', ['school_id' => $a->id]))
            ->assertOk()->assertSee('Rechtenmatrix')->assertSee('Onderwijs');
        $this->put(route('platform.permissions.user.update', [$a, $staff]), [
            'overrides' => [
                'users.manage' => 'allow',
                'evaluations.write' => 'deny',
            ],
        ])->assertRedirect();

        $this->assertTrue(SchoolAccess::allows($staff, $a, 'users.manage'));
        $this->assertFalse(SchoolAccess::allows($staff, $a, 'evaluations.write'));
        $this->assertFalse(SchoolAccess::allows($staff, $b, 'users.manage'));
        $this->assertTrue(SchoolAccess::allows($staff, $b, 'evaluations.write'));

        $this->put(route('platform.permissions.user.update', [$a, $staff]), [
            'overrides' => ['users.manage' => 'inherit', 'evaluations.write' => 'inherit'],
        ])->assertRedirect();
        $this->assertFalse(SchoolAccess::allows($staff, $a, 'users.manage'));
        $this->assertTrue(SchoolAccess::allows($staff, $a, 'evaluations.write'));
    }

    public function test_non_admin_cannot_manage_platform_permissions_or_other_users(): void
    {
        $school = $this->school('A');
        $teacher = User::factory()->create(['is_platform_admin' => false, 'is_active' => true]);
        $other = User::factory()->create(['is_active' => true]);
        $teacher->schools()->attach($school->id, ['role' => 'teacher', 'is_active' => true]);
        $other->schools()->attach($school->id, ['role' => 'teacher', 'is_active' => true]);

        $this->actingAs($teacher)
            ->get(route('platform.permissions.index'))->assertForbidden();
        $this->put(route('platform.permissions.user.update', [$school, $other]), [
            'overrides' => ['users.manage' => 'allow'],
        ])->assertForbidden();
        $this->assertFalse(SchoolAccess::allows($other, $school, 'users.manage'));
    }

    public function test_permission_overrides_are_ignored_when_school_membership_is_inactive(): void
    {
        $school = $this->school('A');
        $admin = User::factory()->create(['is_platform_admin' => true, 'is_active' => true]);
        $user = User::factory()->create(['is_active' => true]);
        $user->schools()->attach($school->id, ['role' => 'teacher', 'is_active' => true]);

        $this->actingAs($admin)->put(route('platform.permissions.user.update', [$school, $user]), [
            'overrides' => ['users.manage' => 'allow'],
        ])->assertRedirect();
        $this->assertTrue(SchoolAccess::allows($user, $school, 'users.manage'));
        $user->schools()->updateExistingPivot($school->id, ['is_active' => false]);
        $this->assertFalse(SchoolAccess::allows($user, $school, 'users.manage'));
    }

    public function test_teacher_can_edit_only_own_profile_and_change_password(): void
    {
        Notification::fake();
        $staff = User::factory()->create([
            'first_name' => 'Oud',
            'last_name' => 'Personeelslid',
            'email' => 'oud@example.test',
            'password' => 'oldpassword123',
            'is_active' => true,
            'is_platform_admin' => false,
        ]);

        $this->actingAs($staff)->get(route('profile.edit'))
            ->assertOk()->assertSee('Mijn profiel');

        $this->put(route('profile.update'), [
            'first_name' => 'Nieuw', 'last_name' => 'Personeelslid',
            'email' => 'nieuw@example.test', 'phone' => '0470000000',
            'is_platform_admin' => '1',
        ])->assertRedirect();
        $this->assertSame('Nieuw', $staff->fresh()->first_name);
        $this->assertFalse($staff->fresh()->is_platform_admin);

        $this->put(route('profile.password'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertSessionHasErrors('current_password');

        $this->put(route('profile.password'), [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect();
        $this->assertTrue(Hash::check('newpassword123', $staff->fresh()->password));
        Notification::assertSentTo($staff, \App\Notifications\WachtwoordGewijzigd::class);
    }
    public function test_dko3_hidden_school_membership_cannot_select_or_access_school_dashboard(): void
    {
        $school = $this->school('HIDDEN');
        $user = User::factory()->create(['is_active' => true]);
        $user->schools()->attach($school->id, ['role' => 'teacher', 'is_active' => true]);
        $user->schools()->updateExistingPivot($school->id, ['dko3_hidden_at' => now()]);

        $this->assertFalse(SchoolAccess::allows($user, $school, 'groups.view'));

        $this->actingAs($user)
            ->get(route('schools.select'))
            ->assertForbidden();

        $this->post(route('schools.select.store'), ['school_id' => $school->id])
            ->assertNotFound();

        $this->withSession(['active_school_id' => $school->id])
            ->get(route('dashboard'))
            ->assertRedirect(route('schools.select'));
    }

    public function test_dko3_hidden_membership_does_not_block_other_active_school(): void
    {
        $hidden = $this->school('HIDDEN');
        $active = $this->school('ACTIVE');
        $user = User::factory()->create(['is_active' => true]);
        $user->schools()->attach($hidden->id, ['role' => 'teacher', 'is_active' => true]);
        $user->schools()->attach($active->id, ['role' => 'teacher', 'is_active' => true]);
        $user->schools()->updateExistingPivot($hidden->id, ['dko3_hidden_at' => now()]);

        $this->actingAs($user)->get(route('schools.select'))
            ->assertOk()
            ->assertSee('Academie ACTIVE')
            ->assertDontSee('Academie HIDDEN');

        $this->post(route('schools.select.store'), ['school_id' => $active->id])
            ->assertRedirect(route('dashboard'));
        $this->get(route('dashboard'))->assertOk();
    }

}
