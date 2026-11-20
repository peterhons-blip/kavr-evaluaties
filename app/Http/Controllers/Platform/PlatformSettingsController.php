<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Support\AccessAudit;
use App\Support\PlatformMailConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PlatformSettingsController extends Controller
{
    public function edit(): View
    {
        $settings = PlatformSetting::query()->first() ?? new PlatformSetting([
            'platform_name' => 'Artelynx',
            'default_timezone' => 'Europe/Brussels',
            'mail_driver' => 'log',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_from_name' => 'Artelynx',
        ]);

        return view('platform.settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'platform_name' => ['required', 'string', 'max:100'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'default_timezone' => ['required', Rule::in(['Europe/Brussels', 'UTC'])],
            'mail_driver' => ['required', Rule::in(['log', 'smtp'])],
            'mail_host' => ['required_if:mail_driver,smtp', 'nullable', 'string', 'max:255'],
            'mail_port' => ['required_if:mail_driver,smtp', 'nullable', 'integer', 'between:1,65535'],
            'mail_encryption' => ['required', Rule::in(['tls', 'ssl', 'none'])],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:1024'],
            'mail_from_address' => ['required_if:mail_driver,smtp', 'nullable', 'email', 'max:255'],
            'mail_from_name' => ['required_if:mail_driver,smtp', 'nullable', 'string', 'max:255'],
        ]);

        if ($data['mail_driver'] === 'smtp' && empty($data['mail_host'])) {
            throw ValidationException::withMessages(['mail_host' => 'SMTP-server is verplicht.']);
        }

        $settings = PlatformSetting::query()->firstOrNew([]);
        if (empty($data['mail_password'])) {
            unset($data['mail_password']); // Laat opgeslagen geheim intact.
        }
        $settings->fill($data);
        $settings->save();

        PlatformMailConfig::fresh();
        AccessAudit::record('platform.settings.updated', null, null, [
            'mail_driver' => $settings->mail_driver,
            'smtp_host' => $settings->mail_host,
            'password_updated' => array_key_exists('mail_password', $data),
        ]);

        return back()->with('success', 'Platforminstellingen opgeslagen. De nieuwe mailconfiguratie is actief.');
    }

    public function test(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'recipient' => ['required', 'email', 'max:255'],
        ]);

        $settings = PlatformSetting::query()->first();
        if (! $settings) {
            throw ValidationException::withMessages(['recipient' => 'Sla eerst de platforminstellingen op.']);
        }

        if ($settings->mail_driver === 'smtp' &&
            (! $settings->mail_host || ! $settings->mail_from_address)) {
            throw ValidationException::withMessages(['recipient' => 'Vul eerst de SMTP-server en afzender in.']);
        }

        try {
            PlatformMailConfig::fresh();

            Mail::raw(
                "Dit is een testbericht van Artelynx.\n\nAls je dit bericht ontvangt, werkt de mailconfiguratie.",
                function ($message) use ($data) {
                    $message->to($data['recipient'])->subject('Artelynx - SMTP testbericht');
                }
            );

            $settings->forceFill(['last_test_at' => now(), 'last_test_result' => 'success'])->save();
            AccessAudit::record('platform.mail.test', null, null, ['success' => true, 'driver' => $settings->mail_driver]);

            if ($settings->mail_driver === 'log') {
                return back()->with('success', 'Logmodus getest: het bericht staat in storage/logs/laravel.log. Er is GEEN echte e-mail verzonden.');
            }

            return back()->with('success', 'SMTP heeft het testbericht zonder fout aanvaard. Controleer de inbox en spammap van het ontvangstadres.');
        } catch (Throwable $exception) {
            report($exception);
            $settings->forceFill(['last_test_at' => now(), 'last_test_result' => 'failed'])->save();
            AccessAudit::record('platform.mail.test', null, null, ['success' => false, 'driver' => $settings->mail_driver]);

            throw ValidationException::withMessages([
                'recipient' => 'De testmail is niet verzonden. Controleer SMTP-server, poort, beveiliging, gebruikersnaam en app-wachtwoord. Details staan in de Laravel-log.',
            ]);
        }
    }
}
