<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationSubject extends Model
{
    protected $fillable = ['school_id', 'education_domain_id', 'name', 'code', 'discipline', 'evaluation_type', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function domain(): BelongsTo { return $this->belongsTo(EducationDomain::class, 'education_domain_id'); }
}
