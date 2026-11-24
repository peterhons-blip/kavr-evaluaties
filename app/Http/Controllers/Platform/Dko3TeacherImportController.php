<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Support\AccessAudit;
use App\Support\Dko3TeacherSpreadsheet;
use App\Support\SchoolRoleCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Dko3TeacherImportController extends Controller
{
    public function create(): View
    {
        $schools = School::orderBy('name')->get();
        return view('platform.users.import', compact('schools'));
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $data = $request->validate([
            'school_id' => ['required','integer','exists:schools,id'],
            'mode' => ['required', \Illuminate\Validation\Rule::in(['update_only', 'full'])],
            'file' => ['required','file','max:5120','mimes:xlsx,csv,txt'],
        ]);
        $school = School::findOrFail($data['school_id']);
        $mode = $data['mode'];
        $file = $data['file'];
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['xlsx','csv'], true)) {
            throw ValidationException::withMessages(['file' => 'Upload een Excelbestand (.xlsx) of CSV-bestand (.csv).']);
        }

        $parsed = Dko3TeacherSpreadsheet::read($file->getRealPath(), $extension);
        if ($parsed['total_errors'] || count($parsed['rows']) === 0) {
            return back()->withInput()->withErrors([
                'file' => 'De import bevat '.$parsed['total_errors'].' fouten. Er is niets gewijzigd.',
            ])->with('import_errors', $parsed['errors']);
        }

        $rows = $parsed['rows'];
        $emails = array_column($rows, 'email');
        $existing = User::whereIn('email', $emails)->pluck('id','email');
        $current = DB::table('school_user as su')
            ->join('users as u','u.id','=','su.user_id')
            ->where('su.school_id', $school->id)
            ->where('su.role','teacher')
            ->whereNull('su.dko3_hidden_at')
            ->get(['su.user_id', 'u.email']);

        $missing = $mode === 'full'
            ? $current->filter(fn ($u) => ! in_array(mb_strtolower($u->email), $emails, true))
            : $current->take(0);

        $stats = [
            'total' => count($rows),
            'new' => count($rows) - $existing->count(),
            'existing' => $existing->count(),
            'deactivate' => $missing->count(),
        ];

        // Opruimen van achtergelaten persoonlijke gegevens uit oudere previews.
        $disk = Storage::disk('local');
        foreach ($disk->files('dko3-imports') as $stale) {
            if ($disk->lastModified($stale) < now()->subDay()->timestamp) {
                $disk->delete($stale);
            }
        }
        $previousToken = (string) $request->session()->get('dko3_import_token', '');
        if (preg_match('/^[A-Za-z0-9]{48}$/', $previousToken)) {
            $disk->delete('dko3-imports/'.$previousToken.'.json');
        }

        // Bestanden staan tijdelijk buiten public; nooit pushen naar Git.
        $token = Str::random(48);
        $path = 'dko3-imports/'.$token.'.json';
        Storage::disk('local')->put($path, json_encode([
            'school_id' => $school->id,
            'mode' => $mode,
            'created_at' => now()->timestamp,
            'rows' => $rows,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        $request->session()->put('dko3_import_token', $token);
        return view('platform.users.import-preview', compact('school','stats','rows','missing','token','mode'));
    }

    public function commit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required','string','size:48'],
            'confirm' => ['required','accepted'],
        ]);
        if (! hash_equals((string) $request->session()->get('dko3_import_token',''), $data['token'])) {
            abort(419);
        }

        $path = 'dko3-imports/'.$data['token'].'.json';
        $disk = Storage::disk('local');
        if (! $disk->exists($path)) abort(419);
        $snapshot = json_decode($disk->get($path), true, 512, JSON_THROW_ON_ERROR);
        if (now()->timestamp - ($snapshot['created_at'] ?? 0) > 3600) {
            $disk->delete($path);
            abort(419, 'Importvoorbeeld verlopen. Upload het bestand opnieuw.');
        }
        $school = School::findOrFail($snapshot['school_id']);
        $rows = $snapshot['rows'] ?? [];
        $mode = $snapshot['mode'] ?? 'update_only';
        if (! in_array($mode, ['update_only', 'full'], true) || ! $rows) abort(422);

        $report = DB::transaction(function () use ($school, $rows, $mode) {
            SchoolRoleCatalog::ensureFor($school);
            DB::table('schools')->where('id',$school->id)->lockForUpdate()->first();

            $present = [];
            $created = 0;
            $updated = 0;
            foreach ($rows as $item) {
                $email = mb_strtolower($item['email']);
                $user = User::where('email', $email)->first();
                if (! $user) {
                    $user = User::create([
                        'name' => trim($item['first_name'].' '.$item['last_name']),
                        'first_name' => $item['first_name'],
                        'last_name' => $item['last_name'],
                        'email' => $email,
                        'password' => Str::random(64),
                        'is_active' => true,
                        'must_change_password' => true,
                        'phone' => $item['phone'] ?: null,
                    ]);
                    $created++;
                } else {
                    // Behoud globale accountstatus en wachtwoord. Namen bijwerken.
                    $updates = [
                        'name' => trim($item['first_name'].' '.$item['last_name']),
                        'first_name' => $item['first_name'],
                        'last_name' => $item['last_name'],
                    ];
                    // Lege telefoongegevens uit DKO3 wissen een bestaande
                    // handmatige waarde niet.
                    if (! empty($item['phone'])) {
                        $updates['phone'] = $item['phone'];
                    }
                    $user->update($updates);
                    $updated++;
                }

                $present[] = $user->id;
                $profile = [];
                foreach ([
                    'dko3_roles', 'dko3_active_assignments',
                    'dko3_subject_groups', 'dko3_locations', 'dko3_subjects',
                ] as $field) {
                    // CSV's zonder deze velden behouden bestaande DKO3-info.
                    if (! empty($item[$field])) {
                        $profile[$field] = $item[$field];
                    }
                }
                $membership = DB::table('school_user')
                    ->where('school_id', $school->id)->where('user_id', $user->id)->first();

                if ($membership) {
                    // Rol van directie of administratie nooit overschrijven.
                    DB::table('school_user')
                        ->where('id',$membership->id)
                        ->update([
                            'is_active' => true,
                            'dko3_hidden_at' => null,
                            'dko3_managed' => true,
                            'dko3_synced_at' => now(),
                            'updated_at' => now(),
                        ] + $profile);
                } else {
                    DB::table('school_user')->insert([
                        'school_id' => $school->id,
                        'user_id' => $user->id,
                        'role' => 'teacher',
                        'is_active' => true,
                        'dko3_hidden_at' => null,
                        'dko3_managed' => true,
                        'dko3_synced_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ] + $profile);
                }
            }

            // Afwezig in DKO3 => alleen leraar-koppelingen in GEKOZEN school.
            // Geen wijziging aan andere scholen, account, directie of platformadmins.
            $query = DB::table('school_user')
                ->where('school_id',$school->id)
                ->where('role','teacher')
                ->whereNull('dko3_hidden_at')
                ->whereNotIn('user_id', $present);
            $deactivated = $mode === 'full' ? (clone $query)->count() : 0;
            if ($mode === 'full') $query->update([
                'is_active' => false,
                'dko3_hidden_at' => now(),
                'updated_at' => now(),
            ]);

            AccessAudit::record('dko3.teachers.imported', $school, null, [
                'created' => $created,
                'updated' => $updated,
                'hidden' => $deactivated,
                'total' => count($rows),
                'mode' => $mode,
            ]);
            return compact('created','updated','deactivated');
        });

        $disk->delete($path);
        $request->session()->forget('dko3_import_token');

        return redirect()->route('platform.users.index',['school_id'=>$school->id])
            ->with('success', "DKO3-import voltooid: {$report['created']} nieuw, {$report['updated']} bijgewerkt, {$report['deactivated']} niet meer aanwezig (gedeactiveerd en verborgen).");
    }
}
