<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\PlatformSetting;
use App\Support\LoginAudit;
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
        // Een expliciet gekozen actieve standaardschool krijgt voorrang.
        // Bij een gedeactiveerde standaard blijven de algemene login en
        // platformbeheer bereikbaar met een veilige fallback.
        $defaultSchoolId = PlatformSetting::query()->value('default_login_school_id');
        $school = $defaultSchoolId
            ? School::with('settings')->whereKey($defaultSchoolId)->where('is_active', true)->first()
            : null;

        $school ??= School::with('settings')->where('code', 'KAVR')->where('is_active', true)->first();
        $school ??= School::with('settings')->where('is_active', true)->orderBy('id')->first();
        $school ??= School::with('settings')->orderBy('id')->firstOrFail();

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
            LoginAudit::record($request, 'login_success', Auth::user());

            return redirect()->route('schools.select');
        }

        LoginAudit::record($request, 'login_failed', null, null, $credentials['email']);

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
        LoginAudit::record($request, 'logout', $request->user(), $request->session()->get('active_school_id'));
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
