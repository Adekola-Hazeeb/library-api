<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles /* Accept multiple roles */
    ): Response {
        $user = $request->user('sanctum');

        if (!$user) {
            abort(401);
        }

        /* Check if user's role is in the allowed roles list */
        if (!in_array($user->role, $roles)) {
            abort(403);
        }

        return $next($request);
    }
}