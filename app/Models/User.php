<?php

namespace App\Models;

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
}
