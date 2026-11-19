<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Algemene loginpagina.
     * Voorlopig standaard Kunstacademie Voeren-Riemst.
     */
    public function showLogin(): View
    {
        // Algemene login blijft bereikbaar voor platformadmins,
        // ook wanneer de standaardacademie gedeactiveerd is.
        $school = School::with('settings')
            ->where('code', 'KAVR')
            ->first()
            ?? School::with('settings')->orderBy('id')->firstOrFail();

        return view('auth.login', compact('school'));
    }

    /**
     * Loginpagina van een specifieke school.
     */
    public function showSchoolLogin(string $slug): View
    {
        $school = School::with('settings')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('auth.login', compact('school'));
    }

    /**
     * Aanmelden.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials + ['is_active' => true])) {
            $request->session()->regenerate();

            return redirect()->route('schools.select');
        }

        return back()
            ->withErrors([
                'email' => 'Ongeldig e-mailadres of wachtwoord.',
            ])
            ->onlyInput('email');
    }

    /**
     * Afmelden.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
