<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Support\SchoolAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        // Middleware active.school has already verified the membership.
        $school = School::query()
            ->with('settings')
            ->withCount(['users' => fn ($query) => $query->wherePivotNull('dko3_hidden_at'), 'locations', 'roles'])
            ->findOrFail($request->session()->get('active_school_id'));

        $canViewUsers = SchoolAccess::allows($request->user(), $school, 'users.view')
            || SchoolAccess::allows($request->user(), $school, 'users.manage');
        $canManageRoles = SchoolAccess::allows($request->user(), $school, 'roles.manage');

        // Gevoelige gebruikers-/auditinformatie alleen ophalen voor beheerders.
        $recentMembers = $canViewUsers
            ? $school->users()
                ->wherePivotNull('dko3_hidden_at')
                ->orderByPivot('created_at', 'desc')
                ->limit(4)
                ->get(['users.id', 'users.name', 'users.first_name', 'users.last_name', 'users.email'])
            : collect();

        $recentEvents = $canManageRoles
            ? DB::table('access_audit_events')
                ->where('school_id', $school->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['action', 'created_at'])
            : collect();

        return view('dashboard', compact(
            'school', 'canViewUsers', 'canManageRoles', 'recentMembers', 'recentEvents'
        ));
    }
}
