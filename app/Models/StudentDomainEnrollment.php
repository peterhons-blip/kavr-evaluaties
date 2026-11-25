<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDomainEnrollment extends Model
{
    protected $fillable=['school_id','student_school_year_id','education_domain_id','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function schoolYear(): BelongsTo { return $this->belongsTo(StudentSchoolYear::class,'student_school_year_id'); }
    public function domain(): BelongsTo { return $this->belongsTo(EducationDomain::class,'education_domain_id'); }
}
