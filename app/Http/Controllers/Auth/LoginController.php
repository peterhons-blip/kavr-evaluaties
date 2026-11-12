<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    // Toon de loginpagina
    public function showLogin(): View
    {
        return view('auth.login');
    }

    // Verwerk een aanmeldpoging
    public function login(Request $request): RedirectResponse
    {
        // Controleer de ingevulde gegevens
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Probeer de gebruiker aan te melden
        if (Auth::attempt($credentials)) {

            // Vernieuw de sessie voor de veiligheid
            $request->session()->regenerate();

            // Ga naar het dashboard
            return redirect()->intended('/dashboard');
        }

        // Verkeerd e-mailadres of wachtwoord
        return back()
            ->withErrors([
                'email' => 'Ongeldig e-mailadres of wachtwoord.',
            ])
            ->onlyInput('email');
    }

    // Meld de gebruiker af
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
