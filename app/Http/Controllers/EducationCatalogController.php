<?php

namespace App\Http\Controllers;

use App\Models\EducationDomain;
use App\Models\EducationSubject;
use App\Models\School;
use App\Support\AccessAudit;
use App\Support\SchoolAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EducationCatalogController extends Controller
{
    private function platform(Request $request): bool
    {
        return $request->routeIs('platform.education.*');
    }

    private function context(Request $request, bool $write = false): array
    {
        if ($this->platform($request)) {
            abort_unless($request->user()?->is_active && $request->user()->isPlatformAdmin(), 403);
            $schoolId = $request->input('school_id');
            $school = $schoolId ? School::findOrFail($schoolId) : School::orderByDesc('is_active')->orderBy('name')->firstOrFail();
            return [$school, true];
        }

        $school = School::findOrFail($request->session()->get('active_school_id'));
        $permission = $write ? 'groups.manage' : 'groups.view';
        abort_unless(SchoolAccess::allows($request->user(), $school, $permission)
            || SchoolAccess::allows($request->user(), $school, 'school.settings'), 403);
        return [$school, false];
    }

    private function redirectTo(Request $request, School $school): RedirectResponse
    {
        $route = $this->platform($request) ? 'platform.education.index' : 'school.education.index';
        return redirect()->route($route, $this->platform($request) ? ['school_id' => $school->id] : []);
    }

    public function index(Request $request): View
    {
        [$school, $platform] = $this->context($request);
        $domains = EducationDomain::where('school_id', $school->id)
            ->withCount('subjects')->orderBy('name')->get();
        $subjects = EducationSubject::where('school_id', $school->id)
            ->with('domain')->orderBy('name')->paginate(40)->withQueryString();
        $canManage = $platform
            || SchoolAccess::allows($request->user(), $school, 'groups.manage')
            || SchoolAccess::allows($request->user(), $school, 'school.settings');

        return view('education.index', [
            'school' => $school, 'platform' => $platform,
            'layout' => $platform ? 'layouts.platform' : 'layouts.school',
            'schools' => $platform ? School::orderByDesc('is_active')->orderBy('name')->get() : collect(),
            'domains' => $domains, 'subjects' => $subjects, 'canManage' => $canManage,
        ]);
    }

    public function storeDomain(Request $request): RedirectResponse
    {
        [$school] = $this->context($request, true);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('education_domains', 'name')->where('school_id', $school->id)],
            'code' => ['nullable', 'string', 'max:50'],
        ]);
        $domain = EducationDomain::create($data + ['school_id' => $school->id, 'is_active' => true]);
        AccessAudit::record('education.domain.created', $school, null, ['domain_id' => $domain->id]);
        return $this->redirectTo($request, $school)->with('success', 'Domein toegevoegd.');
    }

    public function updateDomain(Request $request, EducationDomain $domain): RedirectResponse
    {
        [$school] = $this->context($request, true);
        abort_unless((int) $domain->school_id === (int) $school->id, 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('education_domains', 'name')->where('school_id', $school->id)->ignore($domain->id)],
            'code' => ['nullable', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
        ]);
        $domain->update($data);
        AccessAudit::record('education.domain.updated', $school, null, ['domain_id' => $domain->id]);
        return $this->redirectTo($request, $school)->with('success', 'Domein bijgewerkt.');
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        [$school] = $this->context($request, true);
        $data = $request->validate([
            'education_domain_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:200'],
            'code' => ['nullable', 'string', 'max:60'],
            'discipline' => ['nullable', 'string', 'max:120'],
        ]);
        $domain = EducationDomain::where('school_id', $school->id)
            ->whereKey($data['education_domain_id'])->firstOrFail();
        if (! $domain->is_active) {
            throw ValidationException::withMessages(['education_domain_id' => 'Kies een actief domein.']);
        }
        $exists = EducationSubject::where('school_id', $school->id)
            ->where('education_domain_id', $domain->id)
            ->where('name', $data['name'])
            ->where('discipline', $data['discipline'] ?? null)->exists();
        if ($exists) {
            throw ValidationException::withMessages(['name' => 'Dit vak bestaat al in dit domein.']);
        }
        $subject = EducationSubject::create($data + ['school_id' => $school->id, 'is_active' => true]);
        AccessAudit::record('education.subject.created', $school, null, ['subject_id' => $subject->id]);
        return $this->redirectTo($request, $school)->with('success', 'Vak toegevoegd.');
    }

    public function updateSubject(Request $request, EducationSubject $subject): RedirectResponse
    {
        [$school] = $this->context($request, true);
        abort_unless((int) $subject->school_id === (int) $school->id, 404);
        $data = $request->validate([
            'education_domain_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:200'],
            'code' => ['nullable', 'string', 'max:60'],
            'discipline' => ['nullable', 'string', 'max:120'],
            'is_active' => ['required', 'boolean'],
        ]);
        EducationDomain::where('school_id', $school->id)
            ->whereKey($data['education_domain_id'])->firstOrFail();
        $exists = EducationSubject::where('school_id', $school->id)
            ->where('education_domain_id', $data['education_domain_id'])
            ->where('name', $data['name'])
            ->where('discipline', $data['discipline'] ?? null)
            ->whereKeyNot($subject->id)->exists();
        if ($exists) {
            throw ValidationException::withMessages(['name' => 'Dit vak bestaat al in dit domein.']);
        }
        $subject->update($data);
        AccessAudit::record('education.subject.updated', $school, null, ['subject_id' => $subject->id]);
        return $this->redirectTo($request, $school)->with('success', 'Vak bijgewerkt.');
    }
}
