<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->belongsToOrganization()) {
            abort(403, 'Unauthorized. Organization access required.');
        }

        return $next($request);
    }
}
