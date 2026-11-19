<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccessAuditController extends Controller
{
    public function index(Request $request): View
    {
        $events = DB::table('access_audit_events as e')
            ->leftJoin('users as actor', 'actor.id', '=', 'e.actor_id')
            ->leftJoin('users as subject', 'subject.id', '=', 'e.subject_user_id')
            ->leftJoin('schools as s', 's.id', '=', 'e.school_id')
            ->select([
                'e.id', 'e.action', 'e.details', 'e.created_at',
                'actor.name as actor_name',
                'subject.name as subject_name',
                's.name as school_name',
            ])
            ->orderByDesc('e.id')
            ->paginate(30);

        return view('platform.audit.index', compact('events'));
    }
}
