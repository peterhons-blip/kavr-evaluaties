<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\MailDiagnostics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PlatformMailLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_platform_admins_can_view_mail_logs(): void
    {
        $user = User::factory()->create(['is_platform_admin' => false, 'is_active' => true]);
        $this->actingAs($user)->get(route('platform.audit.mail'))->assertForbidden();

        $admin = User::factory()->create(['is_platform_admin' => true, 'is_active' => true]);
        $this->actingAs($admin)->get(route('platform.audit.mail'))->assertOk();
    }

    public function test_mail_log_shows_safe_error_and_filters_other_audit_events(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true, 'is_active' => true]);
        DB::table('access_audit_events')->insert([
            'actor_id' => $admin->id,
            'school_id' => null,
            'subject_user_id' => null,
            'action' => 'platform.mail.test',
            'details' => json_encode([
                'success' => false,
                'driver' => 'smtp',
                'recipient' => 'receiver@example.test',
                'diagnosis' => MailDiagnostics::classify(new \RuntimeException(
                    '535 5.7.8 Authentication failed with sensitive-password'
                )),
            ]),
            'created_at' => now(),
        ]);

        DB::table('access_audit_events')->insert([
            'actor_id' => $admin->id,
            'school_id' => null,
            'subject_user_id' => null,
            'action' => 'user.updated',
            'details' => null,
            'created_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('platform.audit.mail'))
            ->assertOk()
            ->assertSee('receiver@example.test')
            ->assertSee('SMTP-aanmelding geweigerd')
            ->assertDontSee('sensitive-password')
            ->assertDontSee('user.updated');
    }

    public function test_regular_outgoing_mail_is_logged_without_message_content(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true, 'is_active' => true]);
        $this->actingAs($admin);

        \Illuminate\Support\Facades\Config::set('mail.default', 'log');
        \Illuminate\Support\Facades\Mail::purge('log');
        \Illuminate\Support\Facades\Mail::raw(
            'THIS_CONTENT_MUST_NEVER_APPEAR_IN_THE_AUDIT',
            fn ($message) => $message->to('recipient@example.test')->subject('Wachtwoordbevestiging')
        );

        $this->assertDatabaseHas('access_audit_events', [
            'action' => 'platform.mail.sent',
        ]);
        $this->get(route('platform.audit.mail'))
            ->assertOk()->assertSee('recipient@example.test')
            ->assertDontSee('THIS_CONTENT_MUST_NEVER_APPEAR_IN_THE_AUDIT');
    }

    public function test_diagnostics_do_not_return_raw_exception_or_sensitive_values(): void
    {
        $result = MailDiagnostics::classify(new \RuntimeException(
            '535 5.7.8 Authentication failed: token=verySecretPassword'
        ));

        $this->assertSame('authentication', $result['code']);
        $this->assertStringNotContainsString('verySecretPassword', json_encode($result));
    }
}
