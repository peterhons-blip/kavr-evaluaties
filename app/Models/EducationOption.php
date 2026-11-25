<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationOption extends Model
{
    protected $fillable=['school_id','education_program_id','name','is_active'];
    protected function casts(): array {return ['is_active'=>'boolean'];}
    public function program(): BelongsTo {return $this->belongsTo(EducationProgram::class,'education_program_id');}
}
