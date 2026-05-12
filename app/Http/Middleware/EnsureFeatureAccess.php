<?php

namespace App\Http\Middleware;

use App\Services\Billing\FeatureGate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureAccess
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $venue = $request->user()?->venue;

        abort_unless($venue, 403);

        if (! app(FeatureGate::class)->canUse($venue, $feature)) {
            return redirect()->route('admin.features.locked', ['feature' => $feature]);
        }

        return $next($request);
    }
}
