<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolController extends Controller
{
    /**
     * Overzicht van alle scholen.
     */
    public function index(): View
    {
        $schools = School::query()
            ->withCount('locations')
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
        ]);

        $school->update($validated);

        return redirect()
            ->route('platform.schools.index')
            ->with('success', 'De schoolgegevens zijn bijgewerkt.');
    }
}
