<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\NederlandseWachtwoordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class DutchPasswordNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_link_uses_artelynx_dutch_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'first_name' => 'Lena',
            'is_active' => true,
        ]);

        $status = Password::sendResetLink(['email' => $user->email]);

        $this->assertSame(Password::RESET_LINK_SENT, $status);
        Notification::assertSentTo($user, NederlandseWachtwoordReset::class, function ($notification) use ($user) {
            $message = $notification->toMail($user);

            $this->assertSame('Artelynx | Wachtwoord instellen of opnieuw instellen', $message->subject);
            $this->assertSame('Beste Lena,', $message->greeting);
            $this->assertSame('Wachtwoord instellen', $message->actionText);
            $this->assertStringContainsString('/reset-password/', $message->actionUrl);
            $this->assertStringContainsString('1 dag', implode(' ', array_merge($message->introLines, $message->outroLines)));

            return true;
        });
    }

    public function test_forgot_password_uses_same_dutch_notification_as_setup_link(): void
    {
        Notification::fake();

        $user = User::factory()->create(['is_active' => true]);
        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, NederlandseWachtwoordReset::class);
    }

    public function test_unknown_email_does_not_reveal_account_existence(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'unknown@example.test'])
            ->assertSessionHas('status');

        Notification::assertNothingSent();
    }

    public function test_mail_header_contains_public_png_logo(): void
    {
        config(['app.url' => 'https://www.artelynx.be']);

        $html = view('vendor.mail.html.header', ['slot' => 'Artelynx'])->render();

        $this->assertStringContainsString('artelynx-lynx-128.png', $html);
        $this->assertStringContainsString('https://www.artelynx.be', $html);
    }

    public function test_notification_uses_generic_greeting_without_first_name(): void
    {
        $user = User::factory()->create(['first_name' => null]);
        $message = (new NederlandseWachtwoordReset('example-token'))->toMail($user);

        $this->assertSame('Beste,', $message->greeting);
    }
}
