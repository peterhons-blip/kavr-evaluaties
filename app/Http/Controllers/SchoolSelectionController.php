<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SchoolSelectionController extends Controller
{
    /**
     * Toon de beschikbare omgevingen.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Actieve scholen van deze gebruiker ophalen.
        $schools = $user->schools()
            ->where('schools.is_active', true)
            ->wherePivot('is_active', true)
            ->orderBy('schools.name')
            ->get();

        // Heeft deze gebruiker platformbeheerrechten?
        $isPlatformAdmin = $user->isPlatformAdmin();

        // Niet-actieve gebruiker mag niet verder.
        if (!$user->is_active) {
            abort(403, 'Uw gebruikersaccount is niet actief.');
        }

        // Geen scholen en geen platformbeheerrechten.
        if ($schools->isEmpty() && !$isPlatformAdmin) {
            $request->session()->forget('active_school_id');

            abort(403, 'U heeft geen toegang tot een actieve school.');
        }

        // Gewone gebruiker met slechts één school:
        // automatisch naar het schooldashboard.
        if (!$isPlatformAdmin && $schools->count() === 1) {
            $request->session()->put(
                'active_school_id',
                $schools->first()->id
            );

            return redirect()->route('dashboard');
        }

        // Platformadmins krijgen altijd de omgevingskeuze.
        // Gebruikers met meerdere scholen ook.
        return view('schools.select', compact(
            'schools',
            'isPlatformAdmin'
        ));
    }

    /**
     * Een school kiezen of ernaartoe wisselen.
     */
    public function select(Request $request): RedirectResponse
    {
        if (!$request->user()->is_active) {
            abort(403, 'Uw gebruikersaccount is niet actief.');
        }

        $validated = $request->validate([
            'school_id' => ['required', 'integer'],
        ]);

        // Controleer de school binnen de toegestane scholen.
        $school = $request->user()
            ->schools()
            ->where('schools.id', $validated['school_id'])
            ->where('schools.is_active', true)
            ->wherePivot('is_active', true)
            ->firstOrFail();

        // Vorige schoolselectie vervangen.
        $request->session()->put(
            'active_school_id',
            $school->id
        );

        return redirect()->route('dashboard');
    }
}
