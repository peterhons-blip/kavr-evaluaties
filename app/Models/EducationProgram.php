<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationProgram extends Model
{
    protected $fillable=['school_id','education_domain_id','name','kind','audience','is_active'];
    protected function casts(): array {return ['is_active'=>'boolean'];}
    public function domain(): BelongsTo {return $this->belongsTo(EducationDomain::class,'education_domain_id');}
    public function options(): HasMany {return $this->hasMany(EducationOption::class);}
    public function gradeYears(): HasMany {return $this->hasMany(EducationGradeYear::class);}
}
