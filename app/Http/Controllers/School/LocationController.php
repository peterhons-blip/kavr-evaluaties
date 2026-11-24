<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolLocation;
use App\Support\SchoolAccess;
use App\Support\AccessAudit;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LocationController extends Controller
{
    private function school(Request $request): School
    {
        $school = School::findOrFail($request->session()->get('active_school_id'));
        abort_unless(SchoolAccess::allows($request->user(), $school, 'school.settings'), 403);
        return $school;
    }

    public function index(Request $request): View
    {
        $school = $this->school($request);
        return view('school.locations.index', [
            'school' => $school,
            'locations' => $school->locations()->orderByDesc('is_main_location')->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('school.locations.form', ['school' => $this->school($request), 'location' => new SchoolLocation()]);
    }

    public function edit(Request $request, SchoolLocation $location): View
    {
        $school = $this->school($request);
        abort_unless((int) $location->school_id === (int) $school->id, 404);
        return view('school.locations.form', compact('school','location'));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->school($request);
        $data = $this->validateLocation($request, $school);
        DB::transaction(function () use ($school, $data) {
            School::whereKey($school->id)->lockForUpdate()->firstOrFail();
            if ($data['is_main_location'] && !$data['is_active']) {
                throw ValidationException::withMessages(['is_active' => 'Een hoofdvestiging moet actief zijn.']);
            }
            if (!$data['is_active'] && !$school->locations()->where('is_active', true)->exists()) {
                throw ValidationException::withMessages(['is_active' => 'De eerste vestiging moet actief zijn.']);
            }
            if ($data['is_main_location']) $school->locations()->update(['is_main_location' => false]);
            $school->locations()->create($data);
        });
        AccessAudit::record('school.location.created', $school, null);
        return redirect()->route('school.locations.index')->with('success','Vestigingsplaats toegevoegd.');
    }

    public function update(Request $request, SchoolLocation $location): RedirectResponse
    {
        $school = $this->school($request);
        abort_unless((int) $location->school_id === (int) $school->id, 404);
        $data = $this->validateLocation($request, $school, $location);
        DB::transaction(function () use ($school, $location, $data) {
            School::whereKey($school->id)->lockForUpdate()->firstOrFail();
            $target = $school->locations()->whereKey($location->id)->firstOrFail();
            if ($data['is_main_location'] && !$data['is_active']) {
                throw ValidationException::withMessages(['is_active' => 'Een hoofdvestiging moet actief zijn.']);
            }
            if ($target->is_active && !$data['is_active'] &&
                !$school->locations()->whereKeyNot($target->id)->where('is_active', true)->exists()) {
                throw ValidationException::withMessages(['is_active' => 'De laatste actieve vestiging kan niet worden uitgeschakeld.']);
            }
            if ($data['is_main_location']) {
                $school->locations()->whereKeyNot($target->id)->update(['is_main_location' => false]);
            }
            $target->update($data);
        });
        AccessAudit::record('school.location.updated', $school, null);
        return redirect()->route('school.locations.index')->with('success', 'Vestigingsplaats bijgewerkt.');
    }

    private function validateLocation(Request $request, School $school, ?SchoolLocation $location = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location_number' => ['nullable','string','max:50',
                Rule::unique('school_locations', 'location_number')->where('school_id', $school->id)->ignore($location?->id)],
            'street' => ['nullable','string','max:255'],
            'postal_code' => ['nullable','string','max:20'],
            'city' => ['nullable','string','max:255'],
            'email' => ['nullable','email','max:255'],
            'phone' => ['nullable','string','max:30'],
            'is_main_location' => ['required','boolean'],
            'is_active' => ['required','boolean'],
        ]);
    }
}
