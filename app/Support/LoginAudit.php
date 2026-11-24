<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class LoginAudit
{
    /**
     * Authentication metadata only. Never passwords, tokens, full failed
     * login identifiers or session cookies. Visible to platform admins only.
     */
    public static function record(Request $request, string $event, ?User $user = null, ?int $schoolId = null, ?string $attemptedEmail = null): void
    {
        $hint = null;
        if ($attemptedEmail !== null && $user === null) {
            $email = mb_strtolower(trim($attemptedEmail));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                [$local, $domain] = explode('@', $email, 2);
                $hint = mb_substr($local, 0, 1).'***@'.$domain;
            }
        }
        DB::table('login_events')->insert([
            'user_id' => $user?->id,
            'school_id' => $schoolId,
            'event' => $event,
            'email_hint' => $hint,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
            'created_at' => now(),
        ]);
    }
}
