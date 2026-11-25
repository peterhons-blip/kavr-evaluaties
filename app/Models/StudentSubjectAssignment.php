<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSubjectAssignment extends Model
{
    protected $fillable=['school_id','student_subject_enrollment_id','teacher_user_id','school_location_id','grade_code'];
    public function enrollment(): BelongsTo { return $this->belongsTo(StudentSubjectEnrollment::class,'student_subject_enrollment_id'); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class,'teacher_user_id'); }
    public function location(): BelongsTo { return $this->belongsTo(SchoolLocation::class,'school_location_id'); }
}
