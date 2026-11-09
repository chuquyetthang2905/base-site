<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force all API requests to expect a JSON response.
        // This prevents Laravel from returning HTML error pages to the frontend.
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
