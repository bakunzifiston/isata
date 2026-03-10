<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSystemAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isSystemAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }

        return $next($request);
    }
}
