<?php

namespace App\Http\Controllers;

use App\Models\EducationDomain;
use App\Models\EducationSubject;
use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\Student;
use App\Models\StudentDomainEnrollment;
use App\Models\StudentSchoolYear;
use App\Models\StudentSubjectEnrollment;
use App\Support\AccessAudit;
use App\Support\Dko3EducationSpreadsheet;
use App\Support\SchoolAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Dko3EducationImportController extends Controller
{
    private function platform(Request $request): bool { return $request->routeIs('platform.education.*'); }

    private function school(Request $request): School
    {
        if ($this->platform($request)) {
            abort_unless($request->user()?->is_active && $request->user()->isPlatformAdmin(), 403);
            return School::findOrFail($request->input('school_id'));
        }
        $school = School::findOrFail($request->session()->get('active_school_id'));
        abort_unless(SchoolAccess::allows($request->user(), $school, 'groups.manage')
            || SchoolAccess::allows($request->user(), $school, 'students.manage'), 403);
        return $school;
    }

    private function base(Request $request, School $school): array
    {
        return ['school'=>$school, 'platform'=>$this->platform($request),
            'layout'=>$this->platform($request) ? 'layouts.platform' : 'layouts.school'];
    }

    private function route(Request $request, string $name): string
    {
        return ($this->platform($request) ? 'platform.education.' : 'school.education.').$name;
    }

    private function params(Request $request, School $school, array $extra = []): array
    {
        return ($this->platform($request) ? ['school_id'=>$school->id] : []) + $extra;
    }

    public function form(Request $request): View
    {
        $school = $this->school($request);
        return view('education.import', $this->base($request, $school));
    }

    private function year(string $value): string
    {
        if (! preg_match('/^(20[0-9]{2})-(20[0-9]{2})$/D', $value, $m) || (int)$m[2] !== (int)$m[1] + 1) {
            throw ValidationException::withMessages(['school_year'=>'Ongeldig schooljaar, gebruik bijvoorbeeld 2026-2027.']);
        }
        return $value;
    }

    private static function normalized(string $name): string
    {
        return preg_replace('/\\s+/u',' ', mb_strtolower(trim($name)));
    }

    /**
     * Niet matchen op een gedeeltelijke naam. In het echte DKO3-vakkenbestand
     * ontbreken stamnummers; dubbelzinnige namen blijven dus onopgelost.
     */
    private function resolve(School $school, string $year, string $type, array $rows, array $overrides = []): array
    {
        $errors = [];
        $resolved = [];
        $students = Student::where('school_id',$school->id)->get();
        $byNumber = $students->keyBy('dko3_stamnummer');
        $byName = $students->groupBy(fn ($s) => self::normalized($s->last_name.', '.$s->first_name));
        $domains = EducationDomain::where('school_id',$school->id)->get()->keyBy(fn ($d) => self::normalized($d->code ?? ''));
        $subjects = EducationSubject::where('school_id',$school->id)->get();
        $locations = SchoolLocation::where('school_id',$school->id)->get();
        $teachers = $school->users()->where('school_user.is_active',true)
            ->whereNull('school_user.dko3_hidden_at')->get();
        foreach ($rows as $row) {
            $issues = [];
            $mapped = [];
            if ($type === 'domains') {
                $student = $byNumber->get($row['number']);
                $domain = $domains->get(self::normalized($row['domain_code']));
                if (! $student) $issues[] = 'Onbekend stamnummer';
                if (! $domain || self::normalized($domain->name) !== self::normalized($row['domain_name'])) {
                    $issues[] = 'Domein/code niet gevonden ('. $row['domain'].')';
                }
                if ($student) $mapped['student_id'] = $student->id;
                if ($domain) $mapped['domain_id'] = $domain->id;
            } else {
                $matches = $byName->get(self::normalized($row['student']), collect());
                $choice = isset($overrides[$row['line']]) ? (int)$overrides[$row['line']] : null;
                $chosen = $choice ? $matches->first(fn ($candidate) => (int)$candidate->id === $choice) : null;
                $student = $matches->count() === 1 ? $matches->first() : $chosen;
                if (! $student) $issues[] = $matches->count() === 0
                    ? 'Leerling niet gevonden'
                    : 'Dubbele leerlingnaam: kies het juiste stamnummer';
                $mapped['student_candidates'] = $matches->count() > 1
                    ? $matches->map(fn ($candidate) => [
                        'id'=>$candidate->id,
                        'number'=>$candidate->dko3_stamnummer,
                        'name'=>$candidate->full_name,
                    ])->values()->all()
                    : [];
                $subjectMatches = $subjects->filter(fn ($s) =>
                    self::normalized($s->name) === self::normalized($row['subject']));
                if ($subjectMatches->count() !== 1) $issues[] = 'Vak niet eenduidig gekoppeld aan domein: '.$row['subject'];
                $locMatches = $locations->filter(fn ($l) => self::normalized($l->name) === self::normalized($row['location']));
                if ($locMatches->count() !== 1) $issues[] = 'Vestigingsplaats niet eenduidig gevonden: '.$row['location'];
                $teacherMatches = $teachers->filter(fn ($u) =>
                    self::normalized($u->last_name.', '.$u->first_name) === self::normalized($row['teacher']));
                if ($teacherMatches->count() !== 1) $issues[] = 'Leerkracht niet eenduidig gevonden: '.$row['teacher'];
                if ($student) $mapped['student_id'] = $student->id;
                if ($subjectMatches->count() === 1) $mapped['subject_id'] = $subjectMatches->first()->id;
                if ($locMatches->count() === 1) $mapped['location_id'] = $locMatches->first()->id;
                if ($teacherMatches->count() === 1) $mapped['teacher_id'] = $teacherMatches->first()->id;
                // Een vak kan meerdere docenten hebben; key wordt gevormd uit alle relaties.
                if (! preg_match('/^(?:[1-4]\\.[1-9]|[CS]\\.[1-9])$/iD',$row['grade'])) {
                    $issues[] = 'Ongeldige graad-/leerjaarcode';
                }
            }
            $resolved[] = ['source'=>$row,'ids'=>$mapped,'issues'=>$issues];
            if ($issues && count($errors) < 40) $errors[] = 'Rij '.$row['line'].': '.implode('; ', $issues);
        }
        return [$resolved,$errors,count(array_filter($resolved,fn ($r)=>count($r['issues'])>0))];
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $school = $this->school($request);
        $data = $request->validate([
            'file'=>['required','file','max:5120','mimes:xlsx,csv,txt'],
            'type'=>['required','in:domains,subjects'],
            'school_year'=>['required','string','max:9'],
        ]);
        $year = $this->year($data['school_year']);
        $type = $data['type'];
        $extension = mb_strtolower($data['file']->getClientOriginalExtension());
        if (! in_array($extension,['csv','xlsx'],true)) {
            throw ValidationException::withMessages(['file'=>'Enkel CSV en XLSX zijn toegestaan.']);
        }
        $parsed = Dko3EducationSpreadsheet::read($data['file']->getRealPath(),$extension,$type);
        if ($parsed['total_errors']) {
            return back()->withErrors(['file'=>'De export bevat '.$parsed['total_errors'].' ongeldige rijen; niets opgeslagen.'])
                ->with('import_errors',$parsed['errors']);
        }
        [$resolved,$issues,$blocked] = $this->resolve($school,$year,$type,$parsed['rows']);
        $token=Str::random(48);
        Storage::disk('local')->put('education-imports/'.$token.'.json',json_encode([
            'school_id'=>$school->id,'user_id'=>$request->user()->id,'time'=>now()->timestamp,
            'school_year'=>$year,'type'=>$type,'rows'=>$parsed['rows'],
        ],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
        $request->session()->put('education_import_token',$token);
        $canResolve = $type === 'subjects' && $blocked > 0 &&
            collect($resolved)->every(fn ($entry) =>
                ! $entry['issues'] || (
                    count($entry['issues']) === 1 &&
                    str_starts_with($entry['issues'][0], 'Dubbele leerlingnaam') &&
                    count($entry['ids']['student_candidates'] ?? []) > 1
                ));
        return view('education.preview', $this->base($request,$school) + [
            'token'=>$token,'rows'=>$resolved,'errors'=>$issues,'blocked'=>$blocked,
            'canResolve'=>$canResolve,
            'schoolYear'=>$year,'type'=>$type,'total'=>count($parsed['rows']),
        ]);
    }

    public function commit(Request $request): RedirectResponse
    {
        $school=$this->school($request);
        $data=$request->validate([
            'token'=>['required','string','size:48'],
            'confirm'=>['required','accepted'],
            'student_overrides'=>['nullable','array'],
            'student_overrides.*'=>['required','integer','min:1'],
        ]);
        abort_unless(hash_equals((string)$request->session()->get('education_import_token',''),$data['token']),419);
        $path='education-imports/'.$data['token'].'.json';
        $disk=Storage::disk('local');
        abort_unless($disk->exists($path),419);
        $snapshot=json_decode($disk->get($path),true,512,JSON_THROW_ON_ERROR);
        abort_unless((int)($snapshot['school_id']??0)===$school->id &&
            (int)($snapshot['user_id']??0)===$request->user()->id &&
            now()->timestamp-(int)($snapshot['time']??0)<=3600,419);
        $year=$this->year((string)($snapshot['school_year']??''));
        $type=$snapshot['type']??'';
        abort_unless(in_array($type,['domains','subjects'],true),422);
        $rows=$snapshot['rows']??[];
        abort_unless(is_array($rows) && count($rows)>0 && count($rows)<=20000,422);
        [$resolved,$issues,$blocked]=$this->resolve($school,$year,$type,$rows,$data['student_overrides']??[]);
        if ($blocked) {
            throw ValidationException::withMessages([
                'file'=>'Import gestopt: '.$blocked.' rijen hebben ontbrekende of dubbelzinnige koppelingen. Los dit op en upload opnieuw.',
            ]);
        }
        $count=DB::transaction(function () use ($school,$year,$type,$resolved) {
            DB::table('schools')->where('id',$school->id)->lockForUpdate()->first();
            $count=0;
            foreach ($resolved as $row) {
                $ids=$row['ids'];
                $studentYear=StudentSchoolYear::firstOrCreate(
                    ['student_id'=>$ids['student_id'],'school_year'=>$year],
                    ['school_id'=>$school->id,'status'=>'enrolled']);
                if ($type==='domains') {
                    StudentDomainEnrollment::firstOrCreate([
                        'student_school_year_id'=>$studentYear->id,
                        'education_domain_id'=>$ids['domain_id']],
                        ['school_id'=>$school->id,'is_active'=>true]);
                } else {
                    $subject=EducationSubject::where('school_id',$school->id)
                        ->findOrFail($ids['subject_id']);
                    $enrollment=StudentSubjectEnrollment::firstOrCreate([
                        'student_school_year_id'=>$studentYear->id,
                        'education_subject_id'=>$subject->id],
                        ['school_id'=>$school->id,'grade'=>$row['source']['grade'],
                         'study_year'=>$row['source']['grade'],'is_active'=>true]);
                    \App\Models\StudentSubjectAssignment::firstOrCreate([
                        'student_subject_enrollment_id'=>$enrollment->id,
                        'teacher_user_id'=>$ids['teacher_id'],
                        'school_location_id'=>$ids['location_id'],
                        'grade_code'=>$row['source']['grade']],
                        ['school_id'=>$school->id]);
                }
                $count++;
            }
            AccessAudit::record('dko3.education.imported',$school,null,[
                'type'=>$type,'school_year'=>$year,'rows'=>$count,
            ]);
            return $count;
        });
        $disk->delete($path);
        $request->session()->forget('education_import_token');
        return redirect()->route($this->route($request,'imports'),$this->params($request,$school))
            ->with('success',$count.' DKO3-regels gecontroleerd en verwerkt. Bestaande historische inschrijvingen zijn behouden.');
    }
}
