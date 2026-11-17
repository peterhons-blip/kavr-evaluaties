<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolSettingsController extends Controller
{
    /**
     * Schoolinstellingen tonen.
     */
    public function edit(School $school): View
    {
        $school->load('settings');

        return view('platform.schools.settings.edit', [
            'school' => $school,
        ]);
    }

    /**
     * Schoolinstellingen opslaan.
     */
    public function update(
        Request $request,
        School $school
    ): RedirectResponse {
        $validated = $request->validate([
            'slogan' => [
                'nullable', 'string', 'max:255',
            ],

            'subtitle' => [
                'nullable', 'string', 'max:255',
            ],

            'login_description' => [
                'nullable', 'string', 'max:2000',
            ],

            'primary_color' => [
                'required',
                'regex:/^#[0-9a-fA-F]{6}$/',
            ],

            'secondary_color' => [
                'required',
                'regex:/^#[0-9a-fA-F]{6}$/',
            ],

            'accent_color' => [
                'required',
                'regex:/^#[0-9a-fA-F]{6}$/',
            ],

            'language' => [
                'required',
                Rule::in(['nl', 'fr', 'en', 'de']),
            ],

            'timezone' => [
                'required',
                Rule::in([
                    'Europe/Brussels',
                    'Europe/Amsterdam',
                    'Europe/Paris',
                    'Europe/Berlin',
                    'UTC',
                ]),
            ],

            'logo' => [
                'nullable',
                'file',
                'mimes:png,jpg,jpeg,webp',
                'max:2048',
            ],

            'remove_logo' => [
                'nullable',
                'boolean',
            ],
        ]);

        $existingSettings = DB::table('school_settings')
            ->where('school_id', $school->id)
            ->first();

        $oldLogo = $existingSettings?->logo_path;

        $newLogo = null;

        if ($request->hasFile('logo')) {
            $newLogo = $request->file('logo')->store(
                'school-logos/' . $school->id,
                'public'
            );
        }

        try {
            $settings = [
                'slogan' => $validated['slogan'] ?? null,
                'subtitle' => $validated['subtitle'] ?? null,
                'login_description' =>
                    $validated['login_description'] ?? null,

                'primary_color' =>
                    strtoupper($validated['primary_color']),

                'secondary_color' =>
                    strtoupper($validated['secondary_color']),

                'accent_color' =>
                    strtoupper($validated['accent_color']),

                'language' => $validated['language'],
                'timezone' => $validated['timezone'],
                'updated_at' => now(),
            ];

            if (
                $newLogo !== null ||
                $request->boolean('remove_logo')
            ) {
                $settings['logo_path'] = $newLogo;
            }

            DB::transaction(function () use (
                $school,
                $settings,
                $existingSettings
            ) {
                if ($existingSettings) {
                    DB::table('school_settings')
                        ->where('school_id', $school->id)
                        ->update($settings);
                } else {
                    DB::table('school_settings')->insert(
                        array_merge(
                            $settings,
                            [
                                'school_id' => $school->id,
                                'created_at' => now(),
                            ]
                        )
                    );
                }
            });
        } catch (\Throwable $e) {
            // Nieuwe upload opruimen bij mislukte opslag.
            if ($newLogo !== null) {
                Storage::disk('public')->delete($newLogo);
            }

            throw $e;
        }

        // Oude logo alleen verwijderen na geslaagde opslag.
        if (
            $oldLogo &&
            $oldLogo !== $newLogo &&
            (
                $newLogo !== null ||
                $request->boolean('remove_logo')
            ) &&
            str_starts_with(
                $oldLogo,
                'school-logos/' . $school->id . '/'
            )
        ) {
            Storage::disk('public')->delete($oldLogo);
        }

        return redirect()
            ->route('platform.schools.settings.edit', $school)
            ->with(
                'success',
                'De schoolinstellingen zijn opgeslagen.'
            );
    }
}
