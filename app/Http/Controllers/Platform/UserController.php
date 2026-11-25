<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Support\SchoolRoleCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'role' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'school_status' => ['nullable', Rule::in(['active', 'inactive'])],
            'password' => ['nullable', Rule::in(['not_set', 'set', 'unknown'])],
            'per_page' => ['nullable', Rule::in(['20','50','100'])],
        ]);

        $search = trim($filters['q'] ?? '');
        $schoolId = isset($filters['school_id']) ? (int) $filters['school_id'] : null;
        $roleFilter = $filters['role'] ?? '';
        $statusFilter = $filters['status'] ?? '';
        $schoolStatusFilter = $filters['school_status'] ?? '';
        $passwordFilter = $filters['password'] ?? '';
        $perPage = (int)($filters['per_page'] ?? 20);

        // Combineer school en rol op DEZELFDE koppeling, zodat rollen van
        // een tweede school geen foutieve matches opleveren.
        $users = User::query()
            ->with(['schools' => fn ($q) => $q->select('schools.id', 'schools.name')])
            ->when($search !== '', function ($query) use ($search) {
                $term = '%' . addcslashes($search, '%_\\\\') . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->when($schoolId !== null || $roleFilter !== '' || $schoolStatusFilter !== '', function ($query) use ($schoolId, $roleFilter, $schoolStatusFilter) {
                $query->whereHas('schools', function ($membership) use ($schoolId, $roleFilter, $schoolStatusFilter) {
                    if ($schoolId !== null) {
                        $membership->where('schools.id', $schoolId);
                    }
                    if ($roleFilter !== '') {
                        $membership->where('school_user.role', $roleFilter);
                    }
                    if ($schoolStatusFilter !== '') {
                        $membership->where('school_user.is_active', $schoolStatusFilter === 'active');
                    }
                });
            })
            ->when($statusFilter !== '', fn ($query) => $query->where('is_active', $statusFilter === 'active'))
            ->when($passwordFilter === 'not_set', fn ($query) => $query->where('must_change_password', true))
            ->when($passwordFilter === 'set', fn ($query) => $query->whereNotNull('password_set_at'))
            ->when($passwordFilter === 'unknown', fn ($query) => $query->whereNull('password_set_at')->where('must_change_password',false))
            // Platformbeheerders eerst, daarna hoogste actieve schoolrol.
            // Bij filter op school telt uitsluitend de rol in die school mee.
            ->orderByDesc('is_platform_admin')
            ->orderByRaw(
                "COALESCE((SELECT MIN(CASE rank_su.role
                    WHEN 'school_admin' THEN 1
                    WHEN 'director' THEN 2
                    WHEN 'administration' THEN 3
                    WHEN 'coordinator' THEN 4
                    WHEN 'teacher' THEN 5
                    WHEN 'jury' THEN 6
                    WHEN 'viewer' THEN 7
                    ELSE 8 END)
                    FROM school_user AS rank_su
                    WHERE rank_su.user_id = users.id
                      AND rank_su.is_active = 1
                      AND rank_su.dko3_hidden_at IS NULL"
                . ($schoolId !== null ? " AND rank_su.school_id = ?" : "")
                . "), 99)",
                $schoolId !== null ? [$schoolId] : []
            )
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        $schools = School::orderBy('name')->get(['id', 'name']);
        $roles = DB::table('school_roles')
            ->orderBy('name')
            ->get(['school_id', 'slug', 'name']);

        if ($request->ajax()) {
            return view('platform.users.partials.results', compact('users', 'roleFilter', 'roles', 'schoolId'));
        }

        return view('platform.users.index', compact(
            'users', 'search', 'schoolId', 'roleFilter', 'statusFilter', 'schoolStatusFilter', 'passwordFilter', 'perPage', 'schools', 'roles'
        ));
    }

    public function create(): View
    {
        return $this->form(new User());
    }

    public function edit(User $user): View
    {
        return $this->form($user->load('schools'));
    }

    private function form(User $user): View
    {
        $schools = School::orderBy('name')->get();
        foreach ($schools as $school) {
            SchoolRoleCatalog::ensureFor($school);
        }

        $roles = DB::table('school_roles')->orderBy('name')->get()->groupBy('school_id');
        $assigned = $user->exists
            ? $user->schools->mapWithKeys(fn ($s) => [$s->id => ['role' => $s->pivot->role, 'is_active' => (bool) $s->pivot->is_active, 'dko3_roles' => $s->pivot->dko3_roles, 'dko3_active_assignments' => $s->pivot->dko3_active_assignments, 'dko3_subject_groups' => $s->pivot->dko3_subject_groups, 'dko3_locations' => $s->pivot->dko3_locations, 'dko3_subjects' => $s->pivot->dko3_subjects]])->all()
            : [];

        return view('platform.users.form', compact('user', 'schools', 'roles', 'assigned'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateUser($request);

        DB::transaction(function () use ($data) {
            $user = new User();
            $user->fill([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => strtolower($data['email']),
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'] ?? Str::random(64),
                'is_active' => (bool) $data['is_active'],
                'must_change_password' => empty($data['password']),
                'password_set_at' => empty($data['password']) ? null : now(),
            ]);
            $user->is_platform_admin = (bool) $data['is_platform_admin'];
            $user->save();
            $this->syncSchools($user, $data['memberships'] ?? []);
            \App\Support\AccessAudit::record('user.created', null, $user, [
                'platform_admin' => (bool) $data['is_platform_admin'],
                'schools' => array_keys($data['memberships'] ?? []),
            ]);
        });

        return redirect()->route('platform.users.index')->with('success', 'Gebruiker aangemaakt.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validateUser($request, $user);

        if ($user->is(auth()->user()) && (! $data['is_active'] || ! $data['is_platform_admin'])) {
            throw ValidationException::withMessages(['is_active' => 'Je kunt je eigen platformbeheer of account niet uitschakelen.']);
        }

        DB::transaction(function () use ($user, $data) {
            $changes = [
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => strtolower($data['email']),
                'is_active' => (bool) $data['is_active'],
                'phone' => $data['phone'] ?? null,
            ];
            if (! empty($data['password'])) {
                $changes['password'] = $data['password'];
                $changes['must_change_password'] = false;
                $changes['password_set_at'] = now();
            }

            if (strtolower((string) $user->email) !== strtolower($data['email'])) {
                $user->email_verified_at = null;
            }
            $user->fill($changes);
            $user->is_platform_admin = (bool) $data['is_platform_admin'];
            $user->save();
            if (! empty($data['password'])) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
                Password::deleteToken($user);
            }
            $this->syncSchools($user, $data['memberships'] ?? []);
            \App\Support\AccessAudit::record('user.updated', null, $user, [
                'platform_admin' => (bool) $data['is_platform_admin'],
                'active' => (bool) $data['is_active'],
                'schools' => array_keys($data['memberships'] ?? []),
            ]);
        });

        if (! empty($data['password'])) {
            try {
                $user->notify((new \App\Notifications\WachtwoordGewijzigd())->locale('nl'));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->route('platform.users.index')->with('success', 'Gebruiker bijgewerkt.');
    }

    public function sendSetupLink(User $user): RedirectResponse
    {
        if (! $user->is_active || (! $user->is_platform_admin &&
            ! $user->schools()->where('schools.is_active', true)
                ->wherePivot('is_active', true)
                ->wherePivotNull('dko3_hidden_at')->exists())) {
            throw ValidationException::withMessages([
                'email' => 'Dit personeelsaccount heeft geen actieve toegang. Activeer eerst het account en de schoolkoppeling.',
            ]);
        }

        try {
            $status = Password::sendResetLink(['email' => $user->email]);
        } catch (\Throwable $exception) {
            report($exception);
            $status = null;
        }

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => 'De instellink kon niet verzonden worden. Controleer de mailinstellingen.',
            ]);
        }

        \App\Support\AccessAudit::record('user.setup_link_sent', null, $user);
        return back()->with('success', 'Instellink verzonden naar '.$user->email.'.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'first_name' => ['required','string','max:100'],
            'last_name' => ['required','string','max:100'],
            'phone' => ['nullable','string','max:60'],
            'email' => ['required','email','max:255',Rule::unique('users','email')->ignore($user?->id)],
            'password' => ['nullable','confirmed','string','min:8'],
            'is_active' => ['required','boolean'],
            'is_platform_admin' => ['required','boolean'],
            'memberships' => ['sometimes','array'],
            'memberships.*.role' => ['required','string','max:30'],
            'memberships.*.is_active' => ['required','boolean'],
            'memberships.*.dko3_roles' => ['nullable','string','max:10000'],
            'memberships.*.dko3_active_assignments' => ['nullable','string','max:10000'],
            'memberships.*.dko3_subject_groups' => ['nullable','string','max:10000'],
            'memberships.*.dko3_locations' => ['nullable','string','max:10000'],
            'memberships.*.dko3_subjects' => ['nullable','string','max:10000'],
        ], [
            'email.unique' => 'Dit e-mailadres is al gekoppeld aan een bestaand personeelsaccount. Zoek het personeelslid op en bewerk de bestaande account in plaats van een nieuwe aan te maken.',
            'email.required' => 'Vul een e-mailadres in.',
            'email.email' => 'Vul een geldig e-mailadres in.',
            'first_name.required' => 'Vul de voornaam in.',
            'last_name.required' => 'Vul de achternaam in.',
            'password.min' => 'Het wachtwoord moet minstens 8 tekens bevatten.',
            'password.confirmed' => 'De twee wachtwoorden komen niet overeen.',
            'memberships.*.role.required' => 'Selecteer een schoolrol voor elke aangevinkte school.',
        ]);

        foreach ($data['memberships'] ?? [] as $schoolId => $membership) {
            if (! ctype_digit((string) $schoolId) || ! DB::table('school_roles')
                ->where('school_id', $schoolId)->where('slug', $membership['role'])->exists()) {
                throw ValidationException::withMessages(['memberships' => 'Een rol behoort niet tot de gekozen school.']);
            }
        }

        return $data;
    }

    private function syncSchools(User $user, array $memberships): void
    {
        $sync = [];
        foreach ($memberships as $schoolId => $membership) {
            $sync[(int) $schoolId] = [
                'role' => $membership['role'],
                'is_active' => (bool) $membership['is_active'],
            ];
            foreach (['dko3_roles','dko3_active_assignments','dko3_subject_groups','dko3_locations','dko3_subjects'] as $field) {
                if (array_key_exists($field, $membership)) {
                    $sync[(int) $schoolId][$field] = $membership[$field];
                }
            }
        }
        $user->schools()->sync($sync);
    }
}
