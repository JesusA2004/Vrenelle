<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defense-in-depth gate for the whole /admin area: even if an individual
 * controller method ever forgets its own ->can() check, this stops any
 * authenticated user without at least one admin-facing permission from
 * reaching the panel at all.
 */
class EnsureCanAccessAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user && $user->getAllPermissions()->isNotEmpty(), 403);

        return $next($request);
    }
}
