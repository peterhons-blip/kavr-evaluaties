<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Support\SchoolAccess;
use App\Support\SchoolRoleCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MemberController extends Controller
{
    private function school(Request $request): School
    {
        return School::findOrFail($request->session()->get('active_school_id'));
    }

    private function authorizeSchool(Request $request, string $permission): School
    {
        $school = $this->school($request);
        abort_unless(SchoolAccess::allows($request->user(), $school, $permission), 403);
        return $school;
    }

    public function index(Request $request): View
    {
        $school = $this->school($request);
        abort_unless(
            SchoolAccess::allows($request->user(), $school, 'users.view') ||
            SchoolAccess::allows($request->user(), $school, 'users.manage'),
            403
        );
        SchoolRoleCatalog::ensureFor($school);
        $filters = $request->validate([
            'q' => ['nullable','string','max:120'],
            'role' => ['nullable','string','max:30'],
            'status' => ['nullable',Rule::in(['all','active','inactive'])],
            'password' => ['nullable',Rule::in(['all','not_set','set','unknown'])],
            'per_page' => ['nullable',Rule::in(['20','50','100'])],
        ]);
        $search = trim($filters['q'] ?? '');
        $roleFilter = $filters['role'] ?? '';
        $status = $filters['status'] ?? 'all';
        $passwordFilter = $filters['password'] ?? 'all';
        $perPage = (int) ($filters['per_page'] ?? 20);
        $roles = $school->roles()->orderBy('name')->get();
        if ($roleFilter && ! $roles->contains('slug', $roleFilter)) {
            abort(422, 'Ongeldige schoolrol.');
        }
        $members = $school->users()
            ->whereNull('school_user.dko3_hidden_at')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\\\').'%';
                $query->where(function ($nested) use ($like) {
                    $nested->where('users.first_name','like',$like)
                        ->orWhere('users.last_name','like',$like)
                        ->orWhere('users.name','like',$like)
                        ->orWhere('users.email','like',$like);
                });
            })
            ->when($roleFilter !== '', fn ($query) => $query->where('school_user.role', $roleFilter))
            ->when($status !== 'all', fn ($query) => $query->where('school_user.is_active', $status === 'active'))
            ->when($passwordFilter === 'not_set', fn ($query) => $query->where('users.must_change_password', true))
            ->when($passwordFilter === 'set', fn ($query) => $query->whereNotNull('users.password_set_at')->where('users.must_change_password', false))
            ->when($passwordFilter === 'unknown', fn ($query) => $query->whereNull('users.password_set_at')->where('users.must_change_password', false))
            ->orderBy('users.last_name')->orderBy('users.first_name')->orderBy('users.id')
            ->paginate($perPage)->withQueryString();
        $canManage = SchoolAccess::allows($request->user(), $school, 'users.manage');
        $counts = [
            'total' => $school->users()->whereNull('school_user.dko3_hidden_at')->count(),
            'active' => $school->users()->whereNull('school_user.dko3_hidden_at')->where('school_user.is_active', true)->count(),
        ];
        return view('school.members.index', compact(
            'school','members','roles','canManage','search','roleFilter','status','passwordFilter','perPage','counts'
        ));
    }

    /**
     * Stuur een bestaande reset-/instellink naar een medewerker van de actieve school.
     * Nooit het token/URL weergeven aan een beheerder of in de auditlog.
     */
    public function sendSetupLink(Request $request, User $user): RedirectResponse
    {
        $school = $this->authorizeSchool($request, 'users.manage');
        $member = $school->users()
            ->where('users.id', $user->id)
            ->whereNull('school_user.dko3_hidden_at')
            ->firstOrFail();

        if (! $member->is_active || ! $member->pivot->is_active || ! $school->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Dit account of de schoolkoppeling is inactief. Activeer deze eerst.',
            ]);
        }
        try {
            $status = Password::sendResetLink(['email' => $member->email]);
        } catch (\Throwable $exception) {
            report($exception);
            $status = null;
        }
        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => 'De wachtwoordlink kon niet worden verzonden. Controleer de mailinstellingen.',
            ]);
        }
        \App\Support\AccessAudit::record('school.member.setup_link_sent', $school, $member);
        return back()->with('success', 'Een wachtwoordinstel-/resetlink is per e-mail verzonden naar '.$member->email.'.');
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->authorizeSchool($request, 'users.manage');
        $data = $request->validate([
            'email' => ['required','email','max:255'],
            'first_name' => ['nullable','string','max:100'],
            'last_name' => ['nullable','string','max:100'],
            'role' => ['required','string','max:30'],
        ]);

        if (! $school->roles()->where('slug', $data['role'])->exists()) {
            throw ValidationException::withMessages(['role' => 'Ongeldige schoolrol.']);
        }

        $email = strtolower(trim($data['email']));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        $created = false;

        if (! $user && (empty($data['first_name']) || empty($data['last_name']))) {
            throw ValidationException::withMessages([
                'first_name' => 'Voor een nieuw account zijn voornaam en achternaam vereist.',
            ]);
        }

        DB::transaction(function () use (&$user, &$created, $email, $data, $school) {
            if (! $user) {
                // Onbekend wachtwoord: toegang pas na wachtwoordinstelling via e-mail.
                $user = User::create([
                    'name' => trim($data['first_name'].' '.$data['last_name']),
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $email,
                    'password' => \Illuminate\Support\Str::random(64),
                    'is_active' => true,
                    'must_change_password' => true,
                    'password_set_at' => null,
                ]);
                $created = true;
                \App\Support\AccessAudit::record('school.user.created', $school, $user);
            }

            if ($school->users()->where('users.id', $user->id)->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'Deze gebruiker is al gekoppeld aan de school.',
                ]);
            }

            $school->users()->attach($user->id, [
                'role' => $data['role'], 'is_active' => true,
            ]);
            \App\Support\AccessAudit::record('school.member.added', $school, $user, [
                'role' => $data['role'],
            ]);
        });

        if ($created) {
            try {
                $status = \Illuminate\Support\Facades\Password::sendResetLink(['email' => $email]);
            } catch (\Throwable $exception) {
                report($exception);
                $status = null;
            }
            $message = $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
                ? 'Nieuw account aangemaakt en instellink verstuurd.'
                : 'Account aangemaakt, maar de instellink kon niet verzonden worden. Controleer de mailinstellingen.';
            return back()->with('success', $message);
        }

        return back()->with('success', 'Bestaande gebruiker aan deze school gekoppeld.');
    }

    /**
     * Gegevens en de schoolspecifieke koppeling van één bestaande medewerker.
     * E-mail en naam horen bij het gedeelde account, de rol/status bij deze school.
     */
    public function edit(Request $request, User $user): View
    {
        $school = $this->authorizeSchool($request, 'users.manage');
        $member = $school->users()->where('users.id', $user->id)
            ->whereNull('school_user.dko3_hidden_at')->firstOrFail();
        $roles = $school->roles()->orderBy('name')->get();
        $otherSchoolsCount = $user->schools()
            ->where('schools.id', '!=', $school->id)->count();

        return view('school.members.edit', compact(
            'school', 'member', 'roles', 'otherSchoolsCount'
        ));
    }

    public function updateDetails(Request $request, User $user): RedirectResponse
    {
        $school = $this->authorizeSchool($request, 'users.manage');
        $member = $school->users()->where('users.id', $user->id)
            ->whereNull('school_user.dko3_hidden_at')->firstOrFail();

        // Accountgegevens zijn gedeeld over scholen. Geen wijzigingen aan
        // platformadminaccounts in een gewone schoolcontext.
        abort_if($member->is_platform_admin, 403, 'Een platformaccount wijzig je in Platformbeheer.');

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($member->id)],
            'phone' => ['nullable', 'string', 'max:60'],
            'role' => ['required', 'string', 'max:30'],
            'is_active' => ['required', 'boolean'],
            'confirm_shared_profile' => ['sometimes', 'accepted'],
        ]);

        $role = $school->roles()->where('slug', $data['role'])->first();
        if (! $role) {
            throw ValidationException::withMessages(['role' => 'Deze rol bestaat niet in de actieve school.']);
        }
        $shared = $member->schools()->where('schools.id', '!=', $school->id)->exists();
        $profileChanged = $member->first_name !== $data['first_name']
            || $member->last_name !== $data['last_name']
            || mb_strtolower($member->email) !== mb_strtolower($data['email'])
            || (string) ($member->phone ?? '') !== (string) ($data['phone'] ?? '');

        if ($shared && $profileChanged && ! $request->boolean('confirm_shared_profile')) {
            throw ValidationException::withMessages([
                'confirm_shared_profile' => 'Dit account wordt door meerdere academies gebruikt. Bevestig dat de gewijzigde persoonsgegevens ook daar zichtbaar worden.',
            ]);
        }

        if ($member->is($request->user()) &&
            (! (bool) $data['is_active'] ||
              ! in_array('users.manage', (array) $role->permissions, true))) {
            throw ValidationException::withMessages([
                'role' => 'Je kunt je eigen schoolbeheertoegang niet uitschakelen.',
            ]);
        }

        DB::transaction(function () use ($school, $member, $data, $profileChanged) {
            // Vergrendel het account en raak geen andere school_user-records aan.
            $locked = User::whereKey($member->id)->lockForUpdate()->firstOrFail();
            if ($profileChanged) {
                if (mb_strtolower($locked->email) !== mb_strtolower($data['email'])) {
                    $locked->email_verified_at = null;
                }
                $locked->first_name = $data['first_name'];
                $locked->last_name = $data['last_name'];
                $locked->name = trim($data['first_name'].' '.$data['last_name']);
                $locked->email = mb_strtolower(trim($data['email']));
                $locked->phone = $data['phone'] ?? null;
                $locked->save();
            }
            $school->users()->updateExistingPivot($member->id, [
                'role' => $data['role'],
                'is_active' => (bool) $data['is_active'],
            ]);
            \App\Support\AccessAudit::record('school.member.details_updated', $school, $member, [
                'profile_changed' => $profileChanged,
                'role' => $data['role'],
                'school_active' => (bool) $data['is_active'],
            ]);
        });

        return redirect()->route('school.members.edit', $member)
            ->with('success', 'Personeelsgegevens en schoolkoppeling bijgewerkt.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $school = $this->authorizeSchool($request, 'users.manage');
        abort_unless($school->users()->where('users.id', $user->id)->exists(), 404);

        $data = $request->validate([
            'role' => ['required','string','max:30'],
            'is_active' => ['required','boolean'],
        ]);

        if (! $school->roles()->where('slug', $data['role'])->exists()) {
            throw ValidationException::withMessages(['role' => 'Ongeldige schoolrol.']);
        }

        // Voorkom dat een beheerder zichzelf zonder beheerrechten zet.
        if ($user->is($request->user()) &&
            (! $data['is_active'] || ! in_array('users.manage',
                (array) $school->roles()->where('slug', $data['role'])->first()->permissions, true))) {
            throw ValidationException::withMessages(['role' => 'Je kunt je eigen beheertoegang niet verwijderen.']);
        }

        DB::transaction(function () use ($school, $user, $data) {
            $school->users()->updateExistingPivot($user->id, [
                'role' => $data['role'], 'is_active' => $data['is_active'],
            ]);
            \App\Support\AccessAudit::record('school.member.updated', $school, $user, [
                'role' => $data['role'],
                'active' => (bool) $data['is_active'],
            ]);
        });
        return back()->with('success', 'Schoolrechten bijgewerkt.');
    }
}
