<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SchoolSelectionController extends Controller
{
    /**
     * Toon de scholen waarvoor de gebruiker bevoegd is.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $schools = $request->user()
            ->schools()
            ->where('schools.is_active', true)
            ->wherePivot('is_active', true)
            ->orderBy('schools.name')
            ->get();

        // Geen toegang tot een school
        if ($schools->isEmpty()) {
            $request->session()->forget('active_school_id');

            abort(403, 'U bent niet gekoppeld aan een actieve school.');
        }

        // Slechts één school: automatisch selecteren
        if ($schools->count() === 1) {
            $request->session()->put(
                'active_school_id',
                $schools->first()->id
            );

            return redirect()->route('dashboard');
        }

        return view('schools.select', compact('schools'));
    }

    /**
     * Kies de actieve school.
     */
    public function select(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', 'integer'],
        ]);

        $school = $request->user()
            ->schools()
            ->where('schools.id', $validated['school_id'])
            ->where('schools.is_active', true)
            ->wherePivot('is_active', true)
            ->firstOrFail();

        $request->session()->put('active_school_id', $school->id);

        return redirect()->route('dashboard');
    }
}
