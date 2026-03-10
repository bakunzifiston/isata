<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->belongsToOrganization()) {
            abort(403, 'Unauthorized. Organization access required.');
        }

        if ($user->organization && ! $user->organization->is_active) {
            abort(403, 'Your organization account has been deactivated. Please contact support.');
        }

        return $next($request);
    }
}
