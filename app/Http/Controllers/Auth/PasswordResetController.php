<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function requestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function requestLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required','email']]);

        // Een neutrale bevestiging voorkomt informatielekken
        // over het bestaan van accounts.
        try {
            $status = Password::sendResetLink([
                'email' => strtolower(trim($request->input('email'))),
            ]);

            // Neutrale melding voor bezoekers, maar wel diagnose voor beheerders.
            // Log nooit het adres of het token.
            Log::info('password.reset.request', ['status' => $status]);
        } catch (\Throwable $exception) {
            report($exception);
            Log::warning('password.reset.delivery_failed');
        }

        return back()->with('status',
            'Als dit e-mailadres bekend is, ontvang je een bericht met instructies. Controleer ook je spammap.'
        );
    }

    public function resetForm(string $token, Request $request): View
    {
        $email = (string) $request->query('email', '');
        // Toon een verlopen of al gebruikte link meteen, zonder een wachtwoordformulier.
        // Geen onderscheid maken tussen een onbekend account en een ongeldig token.
        $user = $email !== '' ? User::where('email', $email)->first() : null;
        $invalidLink = ! $user || ! Password::tokenExists($user, $token);

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
            'invalidLink' => $invalidLink,
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required','string'],
            'email' => ['required','email'],
            'password' => ['required','confirmed','string','min:8'],
        ]);

        $status = Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                    'must_change_password' => false,
                    'password_set_at' => now(),
                    'remember_token' => Str::random(60),
                ])->save();
                // Database-sessies van andere apparaten vervallen na reset.
                DB::table('sessions')->where('user_id', $user->id)->delete();
                \App\Support\AccessAudit::record('user.password_reset', null, $user);
                event(new PasswordReset($user));
                // Een mislukte bevestigingsmail mag een geslaagde reset niet terugdraaien.
                try {
                    $user->notify((new \App\Notifications\WachtwoordGewijzigd())->locale('nl'));
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Je wachtwoord is ingesteld. Je kunt nu aanmelden als je account actief is.');
        }

        if (in_array($status, [Password::INVALID_TOKEN, Password::INVALID_USER], true)) {
            return back()->withErrors([
                'token' => 'Deze wachtwoordlink is verlopen, ongeldig of al gebruikt. Vraag hieronder een nieuwe link aan.',
            ])->onlyInput('email');
        }

        // Geef nooit onvertaalde interne statussleutels aan eindgebruikers.
        return back()->withErrors([
            'email' => 'Je wachtwoord kon niet worden gewijzigd. Probeer opnieuw of vraag een nieuwe link aan.',
        ])->onlyInput('email');
    }
}
