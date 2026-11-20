<?php

namespace Tests\Feature;

use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PlatformSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_platform_admin' => true, 'is_active' => true]);
    }

    public function test_only_platform_admins_can_manage_settings(): void
    {
        $teacher = User::factory()->create(['is_platform_admin' => false, 'is_active' => true]);
        $this->actingAs($teacher)->get(route('platform.settings.edit'))->assertForbidden();
        $this->actingAs($teacher)->put(route('platform.settings.update'), [])->assertForbidden();
        $this->actingAs($teacher)->post(route('platform.settings.test-mail'), [
            'recipient' => 'check@example.test',
        ])->assertForbidden();
    }

    public function test_smtp_password_is_encrypted_and_never_prepopulated_in_html(): void
    {
        $this->actingAs($this->admin())->put(route('platform.settings.update'), [
            'platform_name' => 'Artelynx',
            'default_timezone' => 'Europe/Brussels',
            'mail_driver' => 'smtp',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_username' => 'example@example.test',
            'mail_password' => 'TestSecretForDatabaseOnly_23456',
            'mail_from_address' => 'example@example.test',
            'mail_from_name' => 'Artelynx',
        ])->assertSessionHasNoErrors();

        $settings = PlatformSetting::firstOrFail();
        $this->assertSame('TestSecretForDatabaseOnly_23456', $settings->mail_password);
        $this->assertNotSame(
            'TestSecretForDatabaseOnly_23456',
            \Illuminate\Support\Facades\DB::table('platform_settings')->value('mail_password')
        );

        $this->get(route('platform.settings.edit'))
            ->assertOk()
            ->assertDontSee('TestSecretForDatabaseOnly_23456');

        $this->put(route('platform.settings.update'), [
            'platform_name' => 'Artelynx',
            'default_timezone' => 'Europe/Brussels',
            'mail_driver' => 'smtp',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_username' => 'example@example.test',
            'mail_password' => '',
            'mail_from_address' => 'example@example.test',
            'mail_from_name' => 'Artelynx',
        ])->assertSessionHasNoErrors();

        $this->assertSame('TestSecretForDatabaseOnly_23456', $settings->fresh()->mail_password);
    }

    public function test_log_mode_test_mail_has_no_external_delivery(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put(route('platform.settings.update'), [
            'platform_name' => 'Artelynx',
            'default_timezone' => 'Europe/Brussels',
            'mail_driver' => 'log',
            'mail_encryption' => 'tls',
            'mail_from_name' => 'Artelynx',
        ])->assertSessionHasNoErrors();

        Mail::fake();
        $this->post(route('platform.settings.test-mail'), [
            'recipient' => 'check@example.test',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame('success', PlatformSetting::firstOrFail()->last_test_result);
    }
}
