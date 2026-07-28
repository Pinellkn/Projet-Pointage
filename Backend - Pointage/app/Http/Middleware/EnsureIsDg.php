<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsDg
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isDg()) {
            return response()->json([
                'message' => 'Accès réservé à la direction générale.',
            ], 403);
        }

        return $next($request);
    }
}
