<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Support\SchoolAccess;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        // Middleware active.school has already verified the membership.
        $school = School::query()
            ->with('settings')
            ->withCount(['users', 'locations', 'roles'])
            ->findOrFail($request->session()->get('active_school_id'));

        $canViewUsers = SchoolAccess::allows($request->user(), $school, 'users.view')
            || SchoolAccess::allows($request->user(), $school, 'users.manage');
        $canManageRoles = SchoolAccess::allows($request->user(), $school, 'roles.manage');

        return view('dashboard', compact('school', 'canViewUsers', 'canManageRoles'));
    }
}
