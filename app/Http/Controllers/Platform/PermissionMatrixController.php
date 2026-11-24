<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Support\AccessAudit;
use App\Support\SchoolRoleCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionMatrixController extends Controller
{
    public function index(Request $request): View
    {
        $schools = School::query()->orderBy('name')->get();
        $school = $schools->firstWhere('id', (int) $request->query('school_id')) ?? $schools->first();
        $roles = collect();
        $members = collect();
        if ($school) {
            SchoolRoleCatalog::ensureFor($school);
            $roles = $school->roles()->orderBy('name')->get();
            $members = $school->users()->wherePivotNull('dko3_hidden_at')
                ->orderBy('last_name')->orderBy('first_name')
                ->get(['users.id', 'users.first_name', 'users.last_name', 'users.email']);
        }
        return view('platform.permissions.index', compact('schools', 'school', 'roles', 'members'));
    }

    public function user(Request $request, School $school, User $user): View
    {
        abort_unless($school->users()->whereKey($user->id)->exists(), 404);
        SchoolRoleCatalog::ensureFor($school);
        $roleSlug = $school->users()->whereKey($user->id)->firstOrFail()->pivot->role;
        $role = $school->roles()->where('slug', $roleSlug)->firstOrFail();
        $overrides = DB::table('school_user_permissions')
            ->where('school_id', $school->id)
            ->where('user_id', $user->id)
            ->pluck('allowed', 'permission');
        return view('platform.permissions.user', compact('school', 'user', 'role', 'overrides'));
    }

    public function updateUser(Request $request, School $school, User $user): RedirectResponse
    {
        abort_unless($school->users()->whereKey($user->id)->exists(), 404);
        $data = $request->validate([
            'overrides' => ['sometimes', 'array'],
            'overrides.*' => [Rule::in(['inherit', 'allow', 'deny'])],
        ]);
        $submitted = $data['overrides'] ?? [];
        foreach (array_keys($submitted) as $key) {
            abort_unless(array_key_exists($key, SchoolRoleCatalog::PERMISSIONS), 422);
        }

        $roleSlug = $school->users()->whereKey($user->id)->firstOrFail()->pivot->role;
        $role = $school->roles()->where('slug', $roleSlug)->firstOrFail();
        if ($user->id === $request->user()->id &&
            $role->permissions &&
            in_array('roles.manage', $role->permissions, true) &&
            ($submitted['roles.manage'] ?? 'inherit') === 'deny') {
            return back()->withErrors(['overrides' => 'Je kunt je eigen rollenbeheer niet blokkeren.']);
        }

        DB::transaction(function () use ($school, $user, $submitted) {
            DB::table('school_user_permissions')
                ->where('school_id', $school->id)
                ->where('user_id', $user->id)->delete();
            foreach ($submitted as $key => $value) {
                if ($value === 'inherit') continue;
                DB::table('school_user_permissions')->insert([
                    'school_id' => $school->id,
                    'user_id' => $user->id,
                    'permission' => $key,
                    'allowed' => $value === 'allow',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            AccessAudit::record('school.user.permissions.updated', $school, $user, [
                'overrides' => $submitted,
            ]);
        });
        return back()->with('success', 'Individuele rechten bijgewerkt.');
    }
}
