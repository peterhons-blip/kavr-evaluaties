<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\View\View;

class SchoolMemberController extends Controller
{
    public function index(School $school): View
    {
        $members = $school->users()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20);

        $roleNames = $school->roles()->pluck('name','slug');

        return view('platform.schools.users.index', compact(
            'school', 'members', 'roleNames'
        ));
    }
}
