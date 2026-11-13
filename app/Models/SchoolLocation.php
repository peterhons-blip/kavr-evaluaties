<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolLocation extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'location_number',
        'street',
        'postal_code',
        'city',
        'email',
        'phone',
        'is_main_location',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_main_location' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * De school waartoe deze vestigingsplaats behoort.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
