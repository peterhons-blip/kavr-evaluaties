<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        Password::sendResetLink($request->only('email'));

        return back()->with('status',
            'Als dit e-mailadres bekend is, ontvang je een bericht met instructies. Controleer ook je spammap.'
        );
    }

    public function resetForm(string $token, Request $request): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required','string'],
            'email' => ['required','email'],
            'password' => ['required','confirmed','string','min:12'],
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
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Je wachtwoord is ingesteld. Je kunt nu aanmelden als je account actief is.')
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }
}
