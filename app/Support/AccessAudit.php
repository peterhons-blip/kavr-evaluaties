<?php

namespace App\Support;

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class AccessAudit
{
    /**
     * Registreer wijzigingen aan accounts, schoolkoppelingen en rollen.
     * Geen wachtwoorden, tokens of leerlinggegevens in de auditlog.
     */
    public static function record(
        string $action,
        School|int|null $school = null,
        User|int|null $subject = null,
        array $details = []
    ): void {
        DB::table('access_audit_events')->insert([
            'actor_id' => auth()->id(),
            'school_id' => $school instanceof School ? $school->id : $school,
            'subject_user_id' => $subject instanceof User ? $subject->id : $subject,
            'action' => $action,
            'details' => $details ? json_encode($details) : null,
            'created_at' => now(),
        ]);
    }
}
