<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class SchoolLocationController extends Controller
{
    /**
     * Alle vestigingsplaatsen van een school.
     */
    public function index(School $school): View
    {
        $locations = $school->locations()
            ->orderByDesc('is_main_location')
            ->orderBy('name')
            ->get();

        return view('platform.schools.locations.index', compact(
            'school',
            'locations'
        ));
    }

    /**
     * Formulier: nieuwe vestigingsplaats.
     */
    public function create(School $school): View
    {
        return view('platform.schools.locations.create', compact(
            'school'
        ));
    }

    /**
     * Nieuwe vestigingsplaats opslaan.
     */
    public function store(
        Request $request,
        School $school
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'location_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('school_locations', 'location_number')
                    ->where('school_id', $school->id),
            ],

            'street' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_main_location' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);



        DB::transaction(function () use ($school, $validated) {

            // Vergrendel de school tijdens deze wijziging.
            School::whereKey($school->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($validated['is_main_location']) {
                $school->locations()->update([
                    'is_main_location' => false,
                ]);
            }

            $school->locations()->create($validated);

        });


        return redirect()
            ->route('platform.schools.locations.index', $school)
            ->with('success', 'Vestigingsplaats toegevoegd.');
    }

    /**
     * Formulier: vestigingsplaats bewerken.
     */
    public function edit(
        School $school,
        SchoolLocation $location
    ): View {
        $this->ensureBelongsToSchool($school, $location);

        return view('platform.schools.locations.edit', compact(
            'school',
            'location'
        ));
    }

    /**
     * Vestigingsplaats bijwerken.
     */
    public function update(
        Request $request,
        School $school,
        SchoolLocation $location
    ): RedirectResponse {
        $this->ensureBelongsToSchool($school, $location);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'location_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('school_locations', 'location_number')
                    ->where('school_id', $school->id)
                    ->ignore($location->id),
            ],

            'street' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_main_location' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use (
            $school,
            $location,
            $validated
        ) {

            School::whereKey($school->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($validated['is_main_location']) {
                $school->locations()
                    ->whereKeyNot($location->id)
                    ->update(['is_main_location' => false]);
            }

            $location->update($validated);

        });

        return redirect()
            ->route('platform.schools.locations.index', $school)
            ->with('success', 'Vestigingsplaats bijgewerkt.');
    }

    /**
     * Controleer dat de locatie bij de juiste school hoort.
     */
    private function ensureBelongsToSchool(
        School $school,
        SchoolLocation $location
    ): void {
        abort_unless(
            (int) $location->school_id === (int) $school->id,
            404
        );
    }
}
