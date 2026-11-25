<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentSchoolYear extends Model
{
    protected $fillable = ['school_id', 'student_id', 'school_year', 'status'];
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function subjects(): HasMany { return $this->hasMany(StudentSubjectEnrollment::class, 'student_school_year_id'); }
}
