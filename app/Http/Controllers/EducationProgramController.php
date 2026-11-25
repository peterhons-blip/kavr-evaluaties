<?php

namespace App\Http\Controllers;

use App\Models\EducationDomain;
use App\Models\EducationProgram;
use App\Models\EducationOption;
use App\Models\EducationGradeYear;
use App\Models\School;
use App\Support\AccessAudit;
use App\Support\SchoolAccess;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EducationProgramController extends Controller
{
    private function platform(Request $r): bool { return $r->routeIs('platform.education.*'); }

    private function school(Request $r, bool $write = false): School
    {
        if ($this->platform($r)) {
            abort_unless($r->user()?->is_active && $r->user()->isPlatformAdmin(), 403);
            $id = $r->input('school_id');
            return $id ? School::findOrFail($id) : School::orderByDesc('is_active')->orderBy('name')->firstOrFail();
        }
        $school = School::findOrFail($r->session()->get('active_school_id'));
        $right = $write ? 'groups.manage' : 'groups.view';
        abort_unless(SchoolAccess::allows($r->user(), $school, $right)
            || SchoolAccess::allows($r->user(), $school, 'school.settings'), 403);
        return $school;
    }

    private function done(Request $r, School $school, string $message): RedirectResponse
    {
        return redirect()->route($this->platform($r) ? 'platform.education.programs' : 'school.education.programs',
            $this->platform($r) ? ['school_id' => $school->id] : [])->with('success', $message);
    }

    public function index(Request $request): View
    {
        $school = $this->school($request);
        $platform = $this->platform($request);
        return view('education.programs', [
            'layout' => $platform ? 'layouts.platform' : 'layouts.school',
            'school' => $school, 'platform' => $platform,
            'schools' => $platform ? School::orderByDesc('is_active')->orderBy('name')->get() : collect(),
            'domains' => EducationDomain::where('school_id', $school->id)->orderBy('name')->get(),
            'programs' => EducationProgram::where('school_id', $school->id)->with(['domain','options','gradeYears.option'])
                ->orderBy('name')->get(),
            'canManage' => $platform || SchoolAccess::allows($request->user(), $school, 'groups.manage')
                || SchoolAccess::allows($request->user(), $school, 'school.settings'),
        ]);
    }

    public function storeProgram(Request $r): RedirectResponse
    {
        $school = $this->school($r,true);
        $data=$r->validate([
            'education_domain_id'=>['required','integer'],
            'name'=>['required','string','max:160'],
            'kind'=>['required',Rule::in(['regular','short','specialization'])],
            'audience'=>['nullable',Rule::in(['youth','adult','both'])],
        ]);
        EducationDomain::where('school_id',$school->id)->findOrFail($data['education_domain_id']);
        $already=EducationProgram::where('school_id',$school->id)->where('education_domain_id',$data['education_domain_id'])
            ->where('name',$data['name'])->where('kind',$data['kind'])->exists();
        if($already) throw ValidationException::withMessages(['name'=>'Deze opleiding/studierichting bestaat al in dit domein.']);
        $model=EducationProgram::create($data + ['school_id'=>$school->id,'is_active'=>true]);
        AccessAudit::record('education.program.created',$school,null,['program_id'=>$model->id]);
        return $this->done($r,$school,'Opleiding toegevoegd.');
    }

    public function updateProgram(Request $r, EducationProgram $program): RedirectResponse
    {
        $school=$this->school($r,true);
        abort_unless((int)$program->school_id===$school->id,404);
        $data=$r->validate([
            'education_domain_id'=>['required','integer'],
            'name'=>['required','string','max:160'],
            'kind'=>['required',Rule::in(['regular','short','specialization'])],
            'audience'=>['nullable',Rule::in(['youth','adult','both'])],
            'is_active'=>['required','boolean'],
        ]);
        EducationDomain::where('school_id',$school->id)->findOrFail($data['education_domain_id']);
        $program->update($data);
        AccessAudit::record('education.program.updated',$school,null,['program_id'=>$program->id]);
        return $this->done($r,$school,'Opleiding bijgewerkt.');
    }

    public function storeOption(Request $r): RedirectResponse
    {
        $school=$this->school($r,true);
        $data=$r->validate(['education_program_id'=>['required','integer'],'name'=>['required','string','max:150']]);
        $program=EducationProgram::where('school_id',$school->id)->findOrFail($data['education_program_id']);
        $option=EducationOption::firstOrCreate(
            ['education_program_id'=>$program->id,'name'=>$data['name']],
            ['school_id'=>$school->id,'is_active'=>true]);
        AccessAudit::record('education.option.saved',$school,null,['option_id'=>$option->id]);
        return $this->done($r,$school,'Optie toegevoegd.');
    }

    public function updateOption(Request $r, EducationOption $option): RedirectResponse
    {
        $school=$this->school($r,true);
        abort_unless((int)$option->school_id===$school->id,404);
        $data=$r->validate(['name'=>['required','string','max:150'],'is_active'=>['required','boolean']]);
        $option->update($data);
        AccessAudit::record('education.option.updated',$school,null,['option_id'=>$option->id]);
        return $this->done($r,$school,'Optie bijgewerkt.');
    }

    public function storeGradeYear(Request $r): RedirectResponse
    {
        $school=$this->school($r,true);
        $data=$r->validate([
            'education_program_id'=>['required','integer'],
            'education_option_id'=>['nullable','integer'],
            'code'=>['required','string','max:20','regex:/^(?:[1-4]\\.[1-9]|[CS]\\.[1-9])$/iD'],
            'grade'=>['nullable','string','max:20'],
            'study_year'=>['nullable','integer','min:1','max:9'],
        ]);
        $program=EducationProgram::where('school_id',$school->id)->findOrFail($data['education_program_id']);
        $optionId=$data['education_option_id']??null;
        if($optionId) EducationOption::where('school_id',$school->id)->where('education_program_id',$program->id)->findOrFail($optionId);
        $exists=EducationGradeYear::where('education_program_id',$program->id)->where('education_option_id',$optionId)
            ->where('code',strtoupper($data['code']))->exists();
        if($exists) throw ValidationException::withMessages(['code'=>'Dit leerjaar is al aanwezig bij deze optie.']);
        $record=EducationGradeYear::create($data+['school_id'=>$school->id,'is_active'=>true]);
        AccessAudit::record('education.grade_year.created',$school,null,['grade_year_id'=>$record->id]);
        return $this->done($r,$school,'Graad/leerjaar toegevoegd.');
    }

    public function updateGradeYear(Request $r, EducationGradeYear $gradeYear): RedirectResponse
    {
        $school=$this->school($r,true);
        abort_unless((int)$gradeYear->school_id===$school->id,404);
        $data=$r->validate([
            'code'=>['required','string','max:20','regex:/^(?:[1-4]\\.[1-9]|[CS]\\.[1-9])$/iD'],
            'grade'=>['nullable','string','max:20'],
            'study_year'=>['nullable','integer','min:1','max:9'],
            'is_active'=>['required','boolean'],
        ]);
        $gradeYear->update($data);
        AccessAudit::record('education.grade_year.updated',$school,null,['grade_year_id'=>$gradeYear->id]);
        return $this->done($r,$school,'Leerjaar bijgewerkt.');
    }
}
