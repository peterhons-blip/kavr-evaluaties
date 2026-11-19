<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AccessAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserBulkController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_ids' => ['required','array','min:1','max:100'],
            'user_ids.*' => ['required','integer','distinct','exists:users,id'],
            'action' => ['required',Rule::in(['activate','deactivate','set_role','send_setup_links'])],
            'school_id' => ['required_if:action,set_role','nullable','integer','exists:schools,id'],
            'role' => ['required_if:action,set_role','nullable','string','max:30'],
        ]);
        $users = User::whereIn('id',$data['user_ids'])->get();
        $action = $data['action'];

        if ($action === 'set_role') {
            $valid = DB::table('school_roles')
                ->where('school_id',$data['school_id'])
                ->where('slug',$data['role'])->exists();
            if (! $valid) throw ValidationException::withMessages(['role'=>'Kies een bestaande rol voor de gekozen school.']);
        }

        if ($action === 'deactivate' && $users->contains(fn (User $user) => $user->is($request->user()))) {
            throw ValidationException::withMessages(['user_ids'=>'Je kunt jezelf niet in bulk deactiveren.']);
        }

        if ($action === 'send_setup_links') {
            $sent = 0; $failed = 0;
            foreach ($users as $user) {
                if (! $user->is_active || ! $user->schools()
                    ->where('schools.is_active',true)
                    ->wherePivot('is_active',true)
                    ->wherePivotNull('dko3_hidden_at')->exists()) {
                    $failed++; continue;
                }
                try {
                    $status = Password::sendResetLink(['email' => $user->email]);
                    if ($status === Password::RESET_LINK_SENT) {
                        $sent++;
                        AccessAudit::record('user.setup_link_sent', null, $user);
                    } else $failed++;
                } catch (\Throwable $exception) {
                    report($exception);
                    $failed++;
                }
            }
            return back()->with('success',"Wachtwoordinstellinks: {$sent} verstuurd, {$failed} overgeslagen of mislukt.");
        }

        DB::transaction(function () use ($users, $data, $action) {
            foreach ($users as $user) {
                if ($action === 'activate') {
                    $user->update(['is_active'=>true]);
                } elseif ($action === 'deactivate') {
                    $user->update(['is_active'=>false]);
                    DB::table('sessions')->where('user_id',$user->id)->delete();
                } else {
                    // Bewerk alleen een reeds bestaande, zichtbare schoolkoppeling.
                    $membership = DB::table('school_user')
                        ->where('school_id',$data['school_id'])
                        ->where('user_id',$user->id)
                        ->whereNull('dko3_hidden_at')->first();
                    if ($membership) {
                        DB::table('school_user')->where('id',$membership->id)
                            ->update(['role'=>$data['role'],'updated_at'=>now()]);
                    }
                }
                AccessAudit::record('user.bulk.'.$action, $action === 'set_role' ? (int)$data['school_id'] : null, $user, [
                    'role' => $action === 'set_role' ? $data['role'] : null,
                ]);
            }
        });

        return back()->with('success',$users->count().' gebruikers verwerkt. Actie: '.$action.'.');
    }
}
