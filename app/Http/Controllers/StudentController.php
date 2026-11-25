<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Student;
use App\Models\StudentSchoolYear;
use App\Support\AccessAudit;
use App\Support\Dko3StudentSpreadsheet;
use App\Support\SchoolAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentController extends Controller
{
    private function isPlatform(Request $request): bool
    {
        return $request->routeIs('platform.students.*');
    }

    private function school(Request $request, string $permission): School
    {
        if ($this->isPlatform($request)) {
            abort_unless($request->user()?->is_active && $request->user()->isPlatformAdmin(), 403);
            $schoolId = $request->input('school_id');
            if (!$schoolId && $request->routeIs('platform.students.index')) {
                return School::orderByDesc('is_active')->orderBy('name')->firstOrFail();
            }
            return School::findOrFail($schoolId);
        }
        $school = School::findOrFail($request->session()->get('active_school_id'));
        abort_unless(SchoolAccess::allows($request->user(), $school, $permission), 403);
        return $school;
    }

    private function base(Request $request, School $school): array
    {
        return [
            'school' => $school,
            'platform' => $this->isPlatform($request),
            'layout' => $this->isPlatform($request) ? 'layouts.platform' : 'layouts.school',
        ];
    }

    private function routeName(Request $request, string $action): string
    {
        return ($this->isPlatform($request) ? 'platform.students.' : 'school.students.').$action;
    }

    private function params(Request $request, School $school, array $more = []): array
    {
        return $this->isPlatform($request) ? (['school_id' => $school->id] + $more) : $more;
    }

    public function index(Request $request): View
    {
        $school = $this->school($request, 'students.view');
        $query = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'all'])],
            'school_year' => ['nullable', 'regex:/^20[0-9]{2}-20[0-9]{2}$/'],
        ]);
        $search = trim($query['q'] ?? '');
        $status = $query['status'] ?? 'all';
        $schoolYearFilter = $query['school_year'] ?? '';
        $students = Student::query()->with(['schoolYears' => fn ($q) => $q->orderByDesc('school_year')])->where('school_id', $school->id)
            ->when($search !== '', function ($q) use ($search) {
                $term = '%'.addcslashes($search, '%_\\').'%';
                $q->where(function ($nested) use ($term) {
                    $nested->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('dko3_stamnummer', 'like', $term);
                });
            })
            ->when($status !== 'all', fn ($q) => $q->where('is_active', $status === 'active'))
            ->when($schoolYearFilter !== '', fn ($q) => $q->whereHas('schoolYears', fn ($enrollment) => $enrollment->where('school_year', $schoolYearFilter)))
            ->orderBy('last_name')->orderBy('first_name')->orderBy('id')
            ->paginate(25)->withQueryString();

        $canManage = $this->isPlatform($request)
            || SchoolAccess::allows($request->user(), $school, 'students.manage');
        return view('students.index', $this->base($request, $school) + [
            'students' => $students,
            'schools' => $this->isPlatform($request) ? School::orderByDesc('is_active')->orderBy('name')->get() : collect(),
            'search' => $search,
            'status' => $status,
            'schoolYearFilter' => $schoolYearFilter,
            'schoolYears' => StudentSchoolYear::query()->where('school_id', $school->id)->distinct()->orderByDesc('school_year')->pluck('school_year'),
            'canManage' => $canManage,
        ]);
    }

    public function create(Request $request): View
    {
        $school = $this->school($request, 'students.manage');
        return view('students.form', $this->base($request, $school) + ['student' => new Student()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->school($request, 'students.manage');
        $data = $this->validated($request, $school);
        $student = $school->students()->create($data);
        AccessAudit::record('school.student.created', $school, null, ['student_id' => $student->id]);
        return redirect()->route($this->routeName($request, 'index'), $this->params($request, $school))
            ->with('success', 'Leerling toegevoegd.');
    }

    public function edit(Request $request, Student $student): View
    {
        $school = $this->school($request, 'students.manage');
        abort_unless((int) $student->school_id === (int) $school->id, 404);
        return view('students.form', $this->base($request, $school) + ['student' => $student]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $school = $this->school($request, 'students.manage');
        abort_unless((int) $student->school_id === (int) $school->id, 404);
        $student->update($this->validated($request, $school, $student));
        AccessAudit::record('school.student.updated', $school, null, ['student_id' => $student->id]);
        return redirect()->route($this->routeName($request, 'index'), $this->params($request, $school))
            ->with('success', 'Leerling bijgewerkt.');
    }

    private function validated(Request $request, School $school, ?Student $student = null): array
    {
        return $request->validate([
            'dko3_stamnummer' => [
                'required', 'string', 'max:32', 'regex:/^[0-9]+$/D',
                Rule::unique('students', 'dko3_stamnummer')
                    ->where('school_id', $school->id)->ignore($student?->id),
            ],
            'first_name' => ['required', 'string', 'max:150'],
            'last_name' => ['required', 'string', 'max:150'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    public function importForm(Request $request): View
    {
        $school = $this->school($request, 'students.manage');
        return view('students.import', $this->base($request, $school));
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $school = $this->school($request, 'students.manage');
        $data = $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:xlsx,csv,txt'],
            // Optioneel voor oudere API-importclients; de interactieve import vraagt het expliciet.
            'school_year' => ['nullable', 'regex:/^20[0-9]{2}-20[0-9]{2}$/'],
        ]);
        $schoolYear = $data['school_year'] ?? null;
        if ($schoolYear) {
            [$start, $end] = array_map('intval', explode('-', $schoolYear));
            if ($end !== $start + 1) {
                throw ValidationException::withMessages(['school_year' => 'Gebruik een geldig opeenvolgend schooljaar, bijvoorbeeld 2026-2027.']);
            }
        }
        $file = $data['file'];
        $extension = mb_strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['xlsx','csv'], true)) {
            throw ValidationException::withMessages(['file' => 'Gebruik een DKO3-export als XLSX of CSV.']);
        }
        $parsed = Dko3StudentSpreadsheet::read($file->getRealPath(), $extension);
        if ($parsed['total_errors'] > 0) {
            return back()->withErrors(['file' => 'Import gestopt: '.$parsed['total_errors'].' ongeldige of dubbele rijen. Er is niets gewijzigd.'])
                ->with('import_errors', $parsed['errors']);
        }
        $rows = $parsed['rows'];
        $existing = Student::where('school_id', $school->id)
            ->whereIn('dko3_stamnummer', array_column($rows, 'dko3_stamnummer'))
            ->get()->keyBy('dko3_stamnummer');
        $stats = [
            'total' => count($rows),
            'new' => count($rows) - $existing->count(),
            'existing' => $existing->count(),
            'changed' => count(array_filter($rows, fn ($item) => $existing->has($item['dko3_stamnummer']) &&
                ($existing[$item['dko3_stamnummer']]->first_name !== $item['first_name'] ||
                 $existing[$item['dko3_stamnummer']]->last_name !== $item['last_name']))),
        ];

        $disk = Storage::disk('local');
        foreach ($disk->files('student-imports') as $stale) {
            if ($disk->lastModified($stale) < now()->subDay()->timestamp) $disk->delete($stale);
        }
        $previous = (string) $request->session()->get('student_import_token', '');
        if (preg_match('/^[A-Za-z0-9]{48}$/', $previous)) $disk->delete('student-imports/'.$previous.'.json');
        $token = Str::random(48);
        $disk->put('student-imports/'.$token.'.json', json_encode([
            'school_id' => $school->id,
            'user_id' => $request->user()->id,
            'created_at' => now()->timestamp,
            'rows' => $rows,
            'school_year' => $schoolYear,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $request->session()->put('student_import_token', $token);
        return view('students.preview', $this->base($request, $school) + compact('rows', 'stats', 'token', 'schoolYear'));
    }

    public function commit(Request $request): RedirectResponse
    {
        $school = $this->school($request, 'students.manage');
        $data = $request->validate([
            'token' => ['required', 'string', 'size:48', 'regex:/^[A-Za-z0-9]+$/D'],
            'confirm' => ['required', 'accepted'],
        ]);
        if (! hash_equals((string) $request->session()->get('student_import_token', ''), $data['token'])) abort(419);

        $disk = Storage::disk('local');
        $path = 'student-imports/'.$data['token'].'.json';
        if (! $disk->exists($path)) abort(419);
        $snapshot = json_decode($disk->get($path), true, 512, JSON_THROW_ON_ERROR);
        if ((int) ($snapshot['school_id'] ?? 0) !== (int) $school->id ||
            (int) ($snapshot['user_id'] ?? 0) !== (int) $request->user()->id ||
            now()->timestamp - (int) ($snapshot['created_at'] ?? 0) > 3600) {
            $disk->delete($path);
            abort(419, 'De import is verlopen. Upload het bestand opnieuw.');
        }
        $rows = $snapshot['rows'] ?? [];
        if (!is_array($rows) || count($rows) === 0 || count($rows) > 20000) abort(422);

        $schoolYear = $snapshot['school_year'] ?? null;
        $result = DB::transaction(function () use ($school, $rows, $schoolYear) {
            DB::table('schools')->where('id', $school->id)->lockForUpdate()->first();
            $created = 0;
            $updated = 0;
            foreach ($rows as $row) {
                $student = Student::query()
                    ->where('school_id', $school->id)
                    ->where('dko3_stamnummer', $row['dko3_stamnummer'])->first();
                if ($student) {
                    // Manueel gedeactiveerde leerlingen blijven inactief.
                    $student->update(['first_name' => $row['first_name'], 'last_name' => $row['last_name']]);
                    $updated++;
                } else {
                    $student = $school->students()->create($row + ['is_active' => true]);
                    $created++;
                }
                if ($schoolYear) {
                    // Alleen inschrijvingshistoriek bijwerken voor het gekozen jaar.
                    // Handmatig afgesloten jaarinschrijvingen worden niet automatisch heropend.
                    StudentSchoolYear::firstOrCreate(
                        ['student_id' => $student->id, 'school_year' => $schoolYear],
                        ['school_id' => $school->id, 'status' => 'enrolled']
                    );
                }
            }
            // Geen namen/stamnummers opslaan in beheerhistoriek.
            AccessAudit::record('dko3.students.imported', $school, null, [
                'created' => $created, 'updated' => $updated, 'total' => count($rows),
                'mode' => 'add_or_update', 'school_year' => $schoolYear,
            ]);
            return compact('created', 'updated');
        });
        $disk->delete($path);
        $request->session()->forget('student_import_token');
        return redirect()->route($this->routeName($request, 'index'), $this->params($request, $school))
            ->with('success', "DKO3-import geslaagd: {$result['created']} nieuwe, {$result['updated']} bestaande leerlingen bijgewerkt. Niemand werd verwijderd of gedeactiveerd.");
    }
}
