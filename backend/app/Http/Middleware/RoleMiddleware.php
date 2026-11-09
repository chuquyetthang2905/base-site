<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware
 *
 * Protects routes based on the authenticated user's role.
 *
 * Usage in routes:
 *   Route::middleware('role:admin')          — single role
 *   Route::middleware('role:admin,editor')   — any of these roles
 *
 * Flow:
 *   1. JWT middleware (auth:api) runs first — user is guaranteed authenticated here
 *   2. Load roles from the authenticated user
 *   3. Check if user has any of the required roles
 *   4. Pass through or return 403
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        // auth:api middleware ensures user is authenticated before this runs,
        // but we guard defensively in case middleware order changes.
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'data'    => null,
                'errors'  => null,
            ], 401);
        }

        if (! $user->hasAnyRole($roles)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to access this resource',
                'data'    => null,
                'errors'  => null,
            ], 403);
        }

        return $next($request);
    }
}
