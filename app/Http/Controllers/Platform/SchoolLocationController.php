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
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;


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

            School::whereKey($school->id)
                ->lockForUpdate()
                ->firstOrFail();

            $hasActiveLocation = $school->locations()
                ->where('is_active', true)
                ->exists();

            // Er moet minstens één actieve vestiging zijn.
            if (! $hasActiveLocation && ! $validated['is_active']) {
                throw ValidationException::withMessages([
                    'is_active' =>
                        'De eerste vestigingsplaats moet actief zijn.',
                ]);
            }

            // Een hoofdvestiging moet zelf actief zijn.
            if (
                $validated['is_main_location'] &&
                ! $validated['is_active']
            ) {
                throw ValidationException::withMessages([
                    'is_active' =>
                        'Een hoofdvestiging moet actief zijn.',
                ]);
            }

            // Maximaal één hoofdvestiging, maar nul mag ook.
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

            $location = $school->locations()
                ->whereKey($location->id)
                ->firstOrFail();

            $wantsToBeMain = (bool) $validated['is_main_location'];
            $wantsToBeActive = (bool) $validated['is_active'];

            // Een hoofdvestiging moet actief zijn.
            if ($wantsToBeMain && ! $wantsToBeActive) {
                throw ValidationException::withMessages([
                    'is_active' =>
                        'Een hoofdvestiging moet actief zijn.',
                ]);
            }

            // De laatste actieve vestiging mag niet
            // gedeactiveerd worden.
            if ($location->is_active && ! $wantsToBeActive) {
                $otherActiveLocations = $school->locations()
                    ->whereKeyNot($location->id)
                    ->where('is_active', true)
                    ->exists();

                if (! $otherActiveLocations) {
                    throw ValidationException::withMessages([
                        'is_active' =>
                            'Een school moet minstens één actieve '
                            . 'vestigingsplaats behouden.',
                    ]);
                }
            }

            // Alleen bij het kiezen van een hoofdvestiging
            // wordt de hoofdstatus van andere vestigingen verwijderd.
            if ($wantsToBeMain) {
                $school->locations()
                    ->whereKeyNot($location->id)
                    ->update([
                        'is_main_location' => false,
                    ]);
            }

            // Hoofdstatus uitvinken is toegestaan.
            // Hierdoor kunnen alle vestigingen gelijkwaardig zijn.
            $location->update($validated);
        });


        return redirect()
            ->route('platform.schools.locations.index', $school)
            ->with('success', 'Vestigingsplaats bijgewerkt.');
    }

    /**
     * Een ongebruikte vestigingsplaats verwijderen.
     */
    public function destroy(
        School $school,
        SchoolLocation $location
    ): RedirectResponse {
        $this->ensureBelongsToSchool($school, $location);

        try {
            DB::transaction(function () use ($school, $location) {

                School::whereKey($school->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $location = $school->locations()
                    ->whereKey($location->id)
                    ->firstOrFail();

                // De laatste vestigingsplaats mag nooit weg.
                if ($school->locations()->count() <= 1) {
                    throw ValidationException::withMessages([
                        'location' =>
                            'De laatste vestigingsplaats '
                            . 'van een school kan niet verwijderd worden.',
                    ]);
                }

                // Er moet na verwijdering minimaal
                // één actieve vestigingsplaats overblijven.
                $otherActiveLocations = $school->locations()
                    ->whereKeyNot($location->id)
                    ->where('is_active', true)
                    ->exists();

                if (! $otherActiveLocations) {
                    throw ValidationException::withMessages([
                        'location' =>
                            'Een school moet minstens één actieve '
                            . 'vestigingsplaats behouden.',
                    ]);
                }

                // Ook een hoofdvestiging mag worden verwijderd,
                // zolang de andere voorwaarden voldaan zijn.
                $location->delete();
            });

        } catch (QueryException $e) {
            if (in_array((string) $e->getCode(), ['23000', '23503'], true)) {
                return back()->withErrors([
                    'location' =>
                        'Deze vestigingsplaats heeft gekoppelde '
                        . 'gegevens en kan niet verwijderd worden. '
                        . 'Deactiveer de vestiging indien nodig.',
                ]);
            }

            throw $e;
        }

        return redirect()
            ->route('platform.schools.locations.index', $school)
            ->with('success', 'Vestigingsplaats verwijderd.');
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
