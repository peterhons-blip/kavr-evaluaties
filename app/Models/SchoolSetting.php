<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolSetting extends Model
{
    protected $fillable = [
        'school_id',
        'slogan',
        'subtitle',
        'login_description',
        'logo_path',
        'primary_color',
        'secondary_color',
        'accent_color',
        'language',
        'timezone',
    ];

    /**
     * Instellingen horen bij één school.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
