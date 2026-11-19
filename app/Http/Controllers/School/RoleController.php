<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolRole;
use App\Support\SchoolAccess;
use App\Support\SchoolRoleCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RoleController extends Controller
{
    private function school(Request $request): School
    {
        $school = School::findOrFail($request->session()->get('active_school_id'));
        abort_unless(SchoolAccess::allows($request->user(), $school, 'roles.manage'), 403);
        return $school;
    }

    public function index(Request $request): View
    {
        $school = $this->school($request);
        SchoolRoleCatalog::ensureFor($school);
        $roles = $school->roles()->orderByDesc('is_system')->orderBy('name')->get();
        return view('school.roles.index', compact('school','roles'));
    }

    public function create(Request $request): View
    {
        $school = $this->school($request);
        $role = new SchoolRole(['permissions' => []]);
        return view('school.roles.form', compact('school','role'));
    }

    public function edit(Request $request, SchoolRole $role): View
    {
        $school = $this->school($request);
        abort_unless($role->school_id === $school->id, 404);
        return view('school.roles.form', compact('school','role'));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->school($request);
        $data = $this->validated($request, $school);
        $createdRole = $school->roles()->create($data + ['is_system' => false]);
        \App\Support\AccessAudit::record('school.role.created', $school, null, [
            'slug' => $createdRole->slug,
            'permissions' => $createdRole->permissions,
        ]);
        return redirect()->route('school.roles.index')->with('success','Rol aangemaakt.');
    }

    public function update(Request $request, SchoolRole $role): RedirectResponse
    {
        $school = $this->school($request);
        abort_unless($role->school_id === $school->id, 404);
        $data = $this->validated($request, $school, $role);
        if ($role->is_system) {
            unset($data['slug']);
        } elseif ($role->slug !== $data['slug'] &&
            $school->users()->wherePivot('role',$role->slug)->exists()) {
            throw ValidationException::withMessages(['slug'=>'Toegewezen rolcodes kunnen niet gewijzigd worden.']);
        }

        // Voorkom eigen uitsluiting van rollenbeheer.
        $myRole = $request->user()->schools()->where('schools.id',$school->id)->first()?->pivot?->role;
        if ($myRole === $role->slug && ! in_array('roles.manage',$data['permissions'],true)) {
            throw ValidationException::withMessages(['permissions'=>'Je kunt je eigen rollenbeheer niet uitschakelen.']);
        }
        $role->update($data);
        \App\Support\AccessAudit::record('school.role.updated', $school, null, [
            'slug' => $role->slug,
            'permissions' => $role->permissions,
        ]);
        return redirect()->route('school.roles.index')->with('success','Rol bijgewerkt.');
    }

    public function destroy(Request $request, SchoolRole $role): RedirectResponse
    {
        $school = $this->school($request);
        abort_unless($role->school_id === $school->id, 404);
        if ($role->is_system || $school->users()->wherePivot('role',$role->slug)->exists()) {
            throw ValidationException::withMessages(['role'=>'Standaardrollen en toegewezen rollen mogen niet verwijderd worden.']);
        }
        $role->delete();
        \App\Support\AccessAudit::record('school.role.deleted', $school, null, [
            'slug' => $role->slug,
        ]);
        return redirect()->route('school.roles.index')->with('success','Rol verwijderd.');
    }

    private function validated(Request $request, School $school, ?SchoolRole $role = null): array
    {
        $request->merge(['slug'=>\Illuminate\Support\Str::slug((string) $request->input('slug'),'_')]);
        $data = $request->validate([
            'slug'=>['required','alpha_dash','max:30',Rule::unique('school_roles','slug')->where('school_id',$school->id)->ignore($role?->id)],
            'name'=>['required','string','max:100'],
            'description'=>['nullable','string','max:1000'],
            'permissions'=>['sometimes','array'],
            'permissions.*'=>['string',Rule::in(array_keys(SchoolRoleCatalog::PERMISSIONS))],
        ]);
        $data['permissions']=array_values(array_unique($data['permissions']??[]));
        return $data;
    }
}
