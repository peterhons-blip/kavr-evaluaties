<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AccessAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MyProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:60'],
        ], [
            'email.unique' => 'Dit e-mailadres wordt al door een andere account gebruikt.',
        ]);

        $previousEmail = $user->email;
        $user->fill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'email' => mb_strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
        ]);
        if (mb_strtolower($previousEmail) !== mb_strtolower($data['email'])) {
            $user->email_verified_at = null;
        }
        $user->save();
        AccessAudit::record('user.profile.updated', null, $user);
        return back()->with('success', 'Je profiel is bijgewerkt.');
    }

    public function password(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Je huidige wachtwoord is niet correct.',
            'password.min' => 'Je nieuwe wachtwoord moet minstens 8 tekens bevatten.',
            'password.confirmed' => 'De nieuwe wachtwoorden komen niet overeen.',
        ]);

        $user = $request->user();
        $user->forceFill([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
            'password_set_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(60),
        ])->save();
        DB::table('sessions')->where('user_id', $user->id)
            ->where('id', '!=', $request->session()->getId())->delete();
        Password::deleteToken($user);
        AccessAudit::record('user.password_changed', null, $user);

        try {
            $user->notify((new \App\Notifications\WachtwoordGewijzigd())->locale('nl'));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Je wachtwoord is gewijzigd. Andere sessies zijn afgemeld.');
    }
}
