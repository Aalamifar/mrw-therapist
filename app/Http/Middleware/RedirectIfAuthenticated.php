<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            return redirect()->route(match ($user->role) {
                'admin' => 'admin.dashboard',
                'therapist' => 'therapist.dashboard',
                'student' => 'student.dashboard',
            });
        }

        return $next($request);
    }
}