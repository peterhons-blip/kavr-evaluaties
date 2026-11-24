<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoginAuditController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'event' => ['nullable', Rule::in(['login_success','login_failed','logout','school_selected'])],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);
        $event = $filters['event'] ?? '';
        $schoolId = $filters['school_id'] ?? '';
        $search = trim($filters['q'] ?? '');

        $events = DB::table('login_events as e')
            ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
            ->leftJoin('schools as s', 's.id', '=', 'e.school_id')
            ->when($event !== '', fn ($query) => $query->where('e.event', $event))
            ->when($schoolId !== '', fn ($query) => $query->where('e.school_id', $schoolId))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function ($nested) use ($like) {
                    $nested->where('u.name', 'like', $like)
                        ->orWhere('u.email', 'like', $like);
                });
            })
            ->select([
                'e.id', 'e.event', 'e.email_hint', 'e.ip_address',
                'e.user_agent', 'e.created_at',
                'u.name as user_name', 'u.email as user_email',
                's.name as school_name',
            ])
            ->orderByDesc('e.id')
            ->paginate(40)->withQueryString();

        return view('platform.audit.logins', [
            'events' => $events, 'event' => $event, 'schoolId' => $schoolId,
            'search' => $search, 'schools' => School::orderBy('name')->get(),
        ]);
    }
}
