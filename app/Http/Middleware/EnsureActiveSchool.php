<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSchool
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        // Een gedeactiveerd account verliest onmiddellijk toegang,
        // ook als de gebruiker nog een bestaande sessie heeft.
        if (! $request->user() || ! $request->user()->is_active) {
            abort(403, 'Uw gebruikersaccount is niet actief.');
        }

        $schoolId = $request->session()->get('active_school_id');

        if (!$schoolId) {
            return redirect()->route('schools.select');
        }

        $hasAccess = $request->user()
            ->schools()
            ->where('schools.id', $schoolId)
            ->where('schools.is_active', true)
            ->wherePivot('is_active', true)
            ->exists();

        if (!$hasAccess) {
            $request->session()->forget('active_school_id');

            return redirect()->route('schools.select');
        }

        return $next($request);
    }
}
