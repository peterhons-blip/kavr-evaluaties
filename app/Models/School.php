<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class School extends Model
{
    protected $fillable = [
        'name',
        'code',
        'slug',
        'institution_number',
        'email',
        'phone',
        'website',
        'street',
        'postal_code',
        'city',
        'country_code',
        'director_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Gebruikers die aan deze school gekoppeld zijn.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'school_user')
            ->withPivot(['role', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Huisstijl en instellingen van deze school.
     */
    public function settings(): HasOne
    {
        return $this->hasOne(SchoolSetting::class);
    }
}
