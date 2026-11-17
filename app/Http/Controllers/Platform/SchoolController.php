<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;


class SchoolController extends Controller
{
    /**
     * Overzicht van alle scholen.
     */
    public function index(): View
    {
        $schools = School::query()
            ->withCount('locations')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15);

        return view('platform.schools.index', compact('schools'));
    }

    /**
     * Formulier voor nieuwe school.
     */
    public function create(): View
    {
        return view('platform.schools.create');
    }

    /**
     * Nieuwe school opslaan.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'code' => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
                'unique:schools,code',
            ],

            'slug' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                'unique:schools,slug',
            ],

            'institution_number' => [
                'nullable',
                'string',
                'max:30',
                'unique:schools,institution_number',
            ],

            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'size:2'],
            'director_name' => ['nullable', 'string', 'max:255'],
        ]);

        // Normaliseer de unieke identificaties.
        $validated['code'] = strtoupper($validated['code']);
        $validated['slug'] = strtolower($validated['slug']);
        $validated['country_code'] = strtoupper(
            $validated['country_code']
        );

        // Controleer opnieuw na normalisatie.
        validator($validated, [
            'code' => ['unique:schools,code'],
            'slug' => ['unique:schools,slug'],
        ])->validate();

        // School en instellingen samen opslaan.
        DB::transaction(function () use ($validated) {
            $school = School::create($validated);

            // Standaardinstellingen van de school.
            $school->settings()->create([
                'slogan' => 'Ruimte voor talent.',
                'subtitle' => null,
                'login_description' =>
                    'Welkom bij het evaluatieplatform.',
                'primary_color' => '#142b45',
                'secondary_color' => '#59aaa5',
                'accent_color' => '#e5a65b',
                'language' => 'nl',
                'timezone' => 'Europe/Brussels',
            ]);

            // Elke nieuwe school krijgt automatisch
            // een actieve hoofdvestiging.
            $school->locations()->create([
                'name' => $school->name,
                'location_number' => null,
                'street' => $school->street,
                'postal_code' => $school->postal_code,
                'city' => $school->city,
                'email' => $school->email,
                'phone' => $school->phone,
                'is_main_location' => false,
                'is_active' => true,
            ]);
        });


        return redirect()
            ->route('platform.schools.index')
            ->with('success', 'De school is succesvol toegevoegd.');
    }


    /**
     * Formulier om een school te bewerken.
     */
    public function edit(School $school): View
    {
        return view('platform.schools.edit', compact('school'));
    }

    /**
     * Gewijzigde schoolgegevens opslaan.
     */
    public function update(
        Request $request,
        School $school
    ): RedirectResponse {
        // Unieke velden normaliseren vóór de validatie.
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code'))),
            'slug' => strtolower(trim((string) $request->input('slug'))),
            'country_code' => strtoupper(
                trim((string) $request->input('country_code', 'BE'))
            ),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'code' => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
                Rule::unique('schools', 'code')->ignore($school->id),
            ],

            'slug' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('schools', 'slug')->ignore($school->id),
            ],

            'institution_number' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('schools', 'institution_number')
                    ->ignore($school->id),
            ],

            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'size:2'],
            'director_name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $school->update($validated);

        return redirect()
            ->route('platform.schools.index')
            ->with('success', 'De schoolgegevens zijn bijgewerkt.');
    }


        /**
         * Een ongebruikte school definitief verwijderen.
         */
        public function destroy(School $school): RedirectResponse
        {
            $logoPath = null;

            try {
                DB::transaction(function () use ($school, &$logoPath) {

                    // Vergrendel de school.
                    $school = School::whereKey($school->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    // Scholen met gekoppelde gebruikers bewaren.
                    if ($school->users()->exists()) {
                        throw ValidationException::withMessages([
                            'school' =>
                                'Deze school heeft gekoppelde gebruikers. '
                                . 'Deactiveer de school in plaats van '
                                . 'ze te verwijderen.',
                        ]);
                    }

                    // Scholen met schooljaren of evaluatieperiodes
                    // mogen niet verwijderd worden.
                    foreach ([
                        'school_years',
                        'evaluation_periods',
                    ] as $table) {

                        if (
                            Schema::hasTable($table) &&
                            Schema::hasColumn($table, 'school_id') &&
                            DB::table($table)
                                ->where('school_id', $school->id)
                                ->exists()
                        ) {
                            throw ValidationException::withMessages([
                                'school' =>
                                    'Deze school bevat reeds '
                                    . 'schoolgegevens of evaluatieperiodes. '
                                    . 'Verwijderen is niet toegestaan.',
                            ]);
                        }
                    }

                    $logoPath = $school->settings?->logo_path;

                    // Alleen de eigen vestigingen en instellingen
                    // van deze ongebruikte school verwijderen.
                    $school->locations()->delete();
                    $school->settings()->delete();

                    $school->delete();
                });

            } catch (QueryException $e) {

                if (in_array(
                    (string) $e->getCode(),
                    ['23000', '23503'],
                    true
                )) {
                    return back()->withErrors([
                        'school' =>
                            'Deze school heeft gekoppelde gegevens '
                            . 'en kan niet verwijderd worden. '
                            . 'Deactiveer ze indien nodig.',
                    ]);
                }

                throw $e;
            }

            // Eigen geupload logo pas na geslaagde verwijdering opruimen.
            if (
                $logoPath &&
                str_starts_with(
                    $logoPath,
                    'school-logos/' . $school->id . '/'
                )
            ) {
                Storage::disk('public')->delete($logoPath);
            }

            return redirect()
                ->route('platform.schools.index')
                ->with('success', 'De school is verwijderd.');
        }

}
