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
        $school = $this->authorizeSchool($request, 'users.view');
        SchoolRoleCatalog::ensureFor($school);
        $members = $school->users()->orderBy('last_name')->orderBy('first_name')->paginate(20);
        $roles = $school->roles()->orderBy('name')->get();
        $canManage = SchoolAccess::allows($request->user(), $school, 'users.manage');
        return view('school.members.index', compact('school','members','roles','canManage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->authorizeSchool($request, 'users.manage');
        $data = $request->validate([
            'email' => ['required','email','max:255'],
            'first_name' => ['nullable','string','max:100'],
            'last_name' => ['nullable','string','max:100'],
            'role' => ['required','string','max:50'],
        ]);

        if (! $school->roles()->where('slug', $data['role'])->exists()) {
            throw ValidationException::withMessages(['role' => 'Ongeldige schoolrol.']);
        }

        $email = strtolower(trim($data['email']));
        $user = User::where('email', $email)->first();
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

    public function update(Request $request, User $user): RedirectResponse
    {
        $school = $this->authorizeSchool($request, 'users.manage');
        abort_unless($school->users()->where('users.id', $user->id)->exists(), 404);

        $data = $request->validate([
            'role' => ['required','string','max:50'],
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
