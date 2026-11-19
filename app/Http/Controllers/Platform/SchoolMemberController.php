<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class SchoolMemberController extends Controller
{
    public function index(Request $request, School $school): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $search = trim($filters['q'] ?? '');
        $roleFilter = $filters['role'] ?? '';
        $statusFilter = $filters['status'] ?? '';

        $members = $school->users()
            ->when($search !== '', function ($query) use ($search) {
                $term = '%' . addcslashes($search, '%_\\\\') . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->when($roleFilter !== '', fn ($query) => $query->where('school_user.role', $roleFilter))
            ->when($statusFilter !== '', fn ($query) => $query->where('school_user.is_active', $statusFilter === 'active'))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        $roles = $school->roles()->orderBy('name')->get();
        $roleNames = $roles->pluck('name', 'slug');

        if ($request->ajax()) {
            return view('platform.schools.users.partials.results', compact('school', 'members', 'roleNames'));
        }

        return view('platform.schools.users.index', compact(
            'school', 'members', 'roleNames', 'roles', 'search', 'roleFilter', 'statusFilter'
        ));
    }
}
