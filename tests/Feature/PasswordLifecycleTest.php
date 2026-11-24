<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\WachtwoordGewijzigd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_valid_setup_link_sets_a_strong_password(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'must_change_password' => true,
        ]);
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'VeryStrongExamplePassword!2026',
            'password_confirmation' => 'VeryStrongExamplePassword!2026',
        ])->assertRedirect(route('login'));

        $user->refresh();

        $this->assertTrue(Hash::check('VeryStrongExamplePassword!2026', $user->password));
        $this->assertFalse((bool) $user->must_change_password);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_eight_character_password_is_accepted_and_confirmation_is_sent(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'is_active' => true,
            'must_change_password' => true,
        ]);
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Abcdef8!',
            'password_confirmation' => 'Abcdef8!',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('Abcdef8!', $user->fresh()->password));
        Notification::assertSentTo($user, WachtwoordGewijzigd::class);
    }

    public function test_seven_character_password_is_rejected_without_confirmation(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Abcde8!',
            'password_confirmation' => 'Abcde8!',
        ])->assertSessionHasErrors('password');

        Notification::assertNothingSent();
    }

    public function test_an_inactive_account_cannot_log_in_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'disabled@example.test',
            'password' => 'VeryStrongExamplePassword!2026',
            'is_active' => false,
        ]);

        $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => 'VeryStrongExamplePassword!2026',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
