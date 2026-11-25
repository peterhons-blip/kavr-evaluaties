<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationGradeYear extends Model
{
    protected $fillable=['school_id','education_program_id','education_option_id','code','grade','study_year','is_active'];
    protected function casts(): array {return ['is_active'=>'boolean'];}
    public function program(): BelongsTo {return $this->belongsTo(EducationProgram::class,'education_program_id');}
    public function option(): BelongsTo {return $this->belongsTo(EducationOption::class,'education_option_id');}
}
