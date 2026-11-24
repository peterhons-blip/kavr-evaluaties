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
        // Alleen schoolspecifieke teksten en kleuren. Geen globale SMTP, URL of security-instellingen.
        $data = $request->validate([
            'slogan' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'login_description' => ['nullable', 'string', 'max:2000'],
            'primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'language' => ['required', Rule::in(['nl','fr','en','de'])],
            'timezone' => ['required', Rule::in(['Europe/Brussels','Europe/Amsterdam','Europe/Paris','Europe/Berlin','UTC'])],
        ]);
        foreach (['primary_color','secondary_color','accent_color'] as $field) {
            $data[$field] = strtoupper($data[$field]);
        }
        $school->settings()->updateOrCreate(['school_id' => $school->id], $data);
        \App\Support\AccessAudit::record('school.settings.updated', $school, null);
        return redirect()->route('school.settings.edit')->with('success', 'Schoolinstellingen opgeslagen.');
    }
}
