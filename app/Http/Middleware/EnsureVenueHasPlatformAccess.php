<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVenueHasPlatformAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $venue = $request->user()?->venue;

        abort_unless($venue, 403);

        if (! $venue->hasPlatformAccess()) {
            return redirect()->route('admin.billing.index', ['access' => 'expired']);
        }

        return $next($request);
    }
}
