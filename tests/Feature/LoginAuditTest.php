<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Support\SchoolRoleCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_login_and_logout_are_recorded_without_secrets(): void
    {
        $password = 'TemporarySecret123!';
        $user = User::factory()->create([
            'email' => 'test-login@example.test',
            'password' => $password,
            'is_active' => true,
        ]);

        $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => $password,
        ])->assertRedirect(route('schools.select'));

        $this->assertDatabaseHas('login_events', [
            'user_id' => $user->id,
            'event' => 'login_success',
        ]);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertDatabaseHas('login_events', [
            'user_id' => $user->id,
            'event' => 'logout',
        ]);
        $event = \Illuminate\Support\Facades\DB::table('login_events')->first();
        $this->assertStringNotContainsString($password, json_encode($event));
    }

    public function test_failed_login_records_only_masked_identifier(): void
    {
        $this->post(route('login.submit'), [
            'email' => 'unknown.user@example.test',
            'password' => 'WrongPassword',
        ])->assertSessionHasErrors('email');

        $row = \Illuminate\Support\Facades\DB::table('login_events')
            ->where('event', 'login_failed')->first();
        $this->assertNotNull($row);
        $this->assertNull($row->user_id);
        $this->assertSame('u***@example.test', $row->email_hint);
        $this->assertStringNotContainsString('unknown.user', json_encode($row));
        $this->assertStringNotContainsString('WrongPassword', json_encode($row));
    }

    public function test_school_choice_is_logged_without_cross_school_access(): void
    {
        $school = School::create([
            'name' => 'Testacademie', 'code' => 'AUD', 'slug' => 'aud',
            'country_code' => 'BE', 'is_active' => true,
        ]);
        SchoolRoleCatalog::ensureFor($school);
        $user = User::factory()->create(['is_active' => true]);
        $user->schools()->attach($school->id, ['role' => 'teacher', 'is_active' => true]);

        $this->actingAs($user)
            ->post(route('schools.select.store'), ['school_id' => $school->id])
            ->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('login_events', [
            'user_id' => $user->id,
            'school_id' => $school->id,
            'event' => 'school_selected',
        ]);
    }

    public function test_only_platform_admin_can_view_login_history(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_platform_admin' => false]);
        $this->actingAs($user)->get(route('platform.audit.logins'))->assertForbidden();

        $admin = User::factory()->create(['is_active' => true, 'is_platform_admin' => true]);
        $this->actingAs($admin)->get(route('platform.audit.logins'))
            ->assertOk()->assertSee('Aanmeldgeschiedenis');
    }
}
