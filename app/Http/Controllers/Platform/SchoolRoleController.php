<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolRole;
use App\Support\SchoolRoleCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SchoolRoleController extends Controller
{
    public function index(School $school): View
    {
        SchoolRoleCatalog::ensureFor($school);
        $roles = $school->roles()->orderByDesc('is_system')->orderBy('name')->get();
        return view('platform.roles.index', compact('school', 'roles'));
    }

    public function create(School $school): View
    {
        $role = new SchoolRole(['permissions' => []]);
        return view('platform.roles.form', compact('school', 'role'));
    }

    public function edit(School $school, SchoolRole $role): View
    {
        abort_unless((int) $role->school_id === (int) $school->id, 404);
        return view('platform.roles.form', compact('school', 'role'));
    }

    public function store(Request $request, School $school): RedirectResponse
    {
        $data = $this->validateRole($request, $school);
        $createdRole = $school->roles()->create($data + ['is_system' => false]);
        \App\Support\AccessAudit::record('school.role.created', $school, null, [
            'slug' => $createdRole->slug,
            'permissions' => $createdRole->permissions,
        ]);
        return redirect()->route('platform.schools.roles.index', $school)->with('success', 'Schoolrol aangemaakt.');
    }

    public function update(Request $request, School $school, SchoolRole $role): RedirectResponse
    {
        abort_unless((int) $role->school_id === (int) $school->id, 404);
        $data = $this->validateRole($request, $school, $role);
        if ($role->is_system) {
            unset($data['slug']); // Preserve existing school_user.role references.
        } elseif ($role->slug !== $data['slug'] &&
            $school->users()->wherePivot('role', $role->slug)->exists()) {
            throw ValidationException::withMessages([
                'slug' => 'Deze rol is al toegewezen. De rolcode kan niet meer wijzigen.',
            ]);
        }
        $role->update($data);
        \App\Support\AccessAudit::record('school.role.updated', $school, null, [
            'slug' => $role->slug,
            'permissions' => $role->permissions,
        ]);
        return redirect()->route('platform.schools.roles.index', $school)->with('success', 'Rechten bijgewerkt.');
    }

    public function destroy(School $school, SchoolRole $role): RedirectResponse
    {
        abort_unless((int) $role->school_id === (int) $school->id, 404);
        if ($role->is_system || $school->users()->wherePivot('role', $role->slug)->exists()) {
            throw ValidationException::withMessages(['role' => 'Deze standaardrol of een toegewezen rol kan niet verwijderd worden.']);
        }
        $role->delete();
        \App\Support\AccessAudit::record('school.role.deleted', $school, null, [
            'slug' => $role->slug,
        ]);
        return redirect()->route('platform.schools.roles.index', $school)->with('success', 'Schoolrol verwijderd.');
    }

    private function validateRole(Request $request, School $school, ?SchoolRole $role = null): array
    {
        $request->merge(['slug' => \Illuminate\Support\Str::slug((string) $request->input('slug'), '_')]);
        $data = $request->validate([
            'slug' => [
                'required','alpha_dash','max:30',
                Rule::unique('school_roles', 'slug')->where('school_id', $school->id)->ignore($role?->id),
            ],
            'name' => ['required','string','max:100'],
            'description' => ['nullable','string','max:1000'],
            'permissions' => ['sometimes','array'],
            'permissions.*' => ['string',Rule::in(array_keys(SchoolRoleCatalog::PERMISSIONS))],
        ]);
        $data['permissions'] = array_values(array_unique($data['permissions'] ?? []));
        return $data;
    }
}
