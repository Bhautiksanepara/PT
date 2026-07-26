<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLabProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('user.profile.*', 'user.password.*', 'user.logout')) {
            return $next($request);
        }

        $lab = $request->user('lab');
        if ($lab && !$lab->profile_completed_at) {
            return redirect()->route('user.profile.complete')
                ->with('info', 'Complete your laboratory profile to access the participant portal.');
        }

        return $next($request);
    }
}
