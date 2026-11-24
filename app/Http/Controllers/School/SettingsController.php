<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Support\SchoolAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    private function school(Request $request): School
    {
        $school = School::with('settings')->findOrFail($request->session()->get('active_school_id'));
        abort_unless(SchoolAccess::allows($request->user(), $school, 'school.settings'), 403);
        return $school;
    }

    public function edit(Request $request): View
    {
        return view('school.settings.edit', ['school' => $this->school($request)]);
    }

    public function update(Request $request): RedirectResponse
    {
        $school = $this->school($request);
        // Beheer enkel de eigen academie; platforminstellingen blijven centraal.
        $data = $request->validate([
            'slogan' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'login_description' => ['nullable', 'string', 'max:2000'],
            'primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'language' => ['required', Rule::in(['nl', 'fr', 'en', 'de'])],
            'timezone' => ['required', Rule::in(['Europe/Brussels', 'Europe/Amsterdam', 'Europe/Paris', 'Europe/Berlin', 'UTC'])],
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);
        $oldLogo = $school->settings?->logo_path;
        $newLogo = null;
        if ($request->hasFile('logo')) {
            $newLogo = $request->file('logo')->store('school-logos/'.$school->id, 'public');
        }
        $settings = \Illuminate\Support\Arr::only($data, [
            'slogan', 'subtitle', 'login_description', 'primary_color',
            'secondary_color', 'accent_color', 'language', 'timezone',
        ]);
        foreach (['primary_color', 'secondary_color', 'accent_color'] as $field) {
            $settings[$field] = strtoupper($settings[$field]);
        }
        if ($newLogo !== null || $request->boolean('remove_logo')) {
            $settings['logo_path'] = $newLogo;
        }

        try {
            DB::transaction(function () use ($school, $settings) {
                $school->settings()->updateOrCreate(['school_id' => $school->id], $settings);
                \App\Support\AccessAudit::record('school.settings.updated', $school, null, [
                    'fields' => array_keys($settings),
                ]);
            });
        } catch (\Throwable $exception) {
            if ($newLogo !== null) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($newLogo);
            }
            throw $exception;
        }

        if ($oldLogo && $oldLogo !== $newLogo &&
            ($newLogo !== null || $request->boolean('remove_logo')) &&
            str_starts_with($oldLogo, 'school-logos/'.$school->id.'/')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldLogo);
        }

        return redirect()->route('school.settings.edit')
            ->with('success', 'Schoolinstellingen opgeslagen.');
    }
}
