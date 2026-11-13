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
