<?php

namespace App\Support;

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class SchoolAccess
{
    public static function allows(?User $user, School|int $school, string $permission): bool
    {
        if (! $user || ! $user->is_active || ! array_key_exists($permission, SchoolRoleCatalog::PERMISSIONS)) {
            return false;
        }

        $schoolId = $school instanceof School ? $school->id : $school;

        // Platformbeheer is een afzonderlijke bevoegdheid, geen inzagerecht
        // in vertrouwelijke evaluatiegegevens.
        $pivot = DB::table('school_user')
            ->join('schools', 'schools.id', '=', 'school_user.school_id')
            ->where('school_user.user_id', $user->id)
            ->where('school_user.school_id', $schoolId)
            ->where('school_user.is_active', true)
            ->where('schools.is_active', true)
            ->select('school_user.role')
            ->first();

        if (! $pivot) {
            return false;
        }

        $role = DB::table('school_roles')
            ->where('school_id', $schoolId)
            ->where('slug', $pivot->role)
            ->first();

        if (! $role) {
            return false;
        }

        return in_array($permission, json_decode($role->permissions, true) ?: [], true);
    }
}
