<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->belongsToOrganization()) {
            abort(403, 'Unauthorized. Organization access required.');
        }

        // Only block when is_active column exists and is explicitly false
        if ($user->organization
            && Schema::hasColumn('organizations', 'is_active')
            && $user->organization->is_active === false
        ) {
            abort(403, 'Your organization account has been deactivated. Please contact support.');
        }

        return $next($request);
    }
}
