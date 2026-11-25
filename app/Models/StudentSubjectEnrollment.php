<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSubjectEnrollment extends Model
{
    protected $fillable = ['school_id', 'student_school_year_id', 'education_subject_id', 'grade', 'study_year', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function schoolYear(): BelongsTo { return $this->belongsTo(StudentSchoolYear::class, 'student_school_year_id'); }
    public function subject(): BelongsTo { return $this->belongsTo(EducationSubject::class, 'education_subject_id'); }
    public function assignments(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(StudentSubjectAssignment::class,'student_subject_enrollment_id'); }
}
