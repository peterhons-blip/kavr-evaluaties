<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationSubjectOffering extends Model
{
    protected $fillable=['school_id','education_grade_year_id','education_subject_id',
        'school_year','is_elective','hours_per_week','allow_alternative_context','is_active'];
    protected function casts(): array {return ['is_elective'=>'boolean','allow_alternative_context'=>'boolean','is_active'=>'boolean'];}
    public function gradeYear(): BelongsTo {return $this->belongsTo(EducationGradeYear::class,'education_grade_year_id');}
    public function subject(): BelongsTo {return $this->belongsTo(EducationSubject::class,'education_subject_id');}
}
