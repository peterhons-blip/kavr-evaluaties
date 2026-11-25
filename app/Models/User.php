<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * Velden die we mogen invullen bij het
     * aanmaken of wijzigen van een gebruiker.
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'is_active',
        'must_change_password',
        'password_set_at',
        'phone',
    ];

    /**
     * Velden die niet zichtbaar mogen zijn
     * wanneer gebruikersgegevens worden omgezet
     * naar bijvoorbeeld JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Automatische omzetting van databankvelden.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'password_set_at' => 'datetime',
            'is_platform_admin' => 'boolean',
        ];
    }

    /**
     * Volledige naam samenstellen uit
     * voornaam en achternaam.
     *
     * Gebruik: $user->full_name
     */
    public function getFullNameAttribute(): string
    {
        return trim(
            ($this->first_name ?? '') . ' ' .
            ($this->last_name ?? '')
        );
    }

    /**
     * Controleren of de gebruiker actief is.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Controleren of de gebruiker zijn
     * wachtwoord nog moet wijzigen.
     */
    public function mustChangePassword(): bool
    {
        return (bool) $this->must_change_password;
    }

    /**
     * Scholen waaraan deze gebruiker gekoppeld is.
     */
    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'school_user')
            ->withPivot(['role', 'is_active', 'dko3_hidden_at', 'dko3_roles', 'dko3_active_assignments', 'dko3_subject_groups', 'dko3_locations', 'dko3_subjects'])
            ->withTimestamps();
    }
    
    /**
     * Gebruik voor zowel wachtwoord vergeten als instellinks de Nederlandse mail.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify((new \App\Notifications\NederlandseWachtwoordReset($token))->locale('nl'));
    }

    public function isPlatformAdmin(): bool
    {
        return (bool) $this->is_platform_admin;
    }
    /**
     * Check een schoolgebonden recht. Platformbeheer is een afzonderlijke rol.
     */
    public function hasSchoolPermission(\App\Models\School|int $school, string $permission): bool
    {
        return \App\Support\SchoolAccess::allows($this, $school, $permission);
    }
}
