<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $role = $request->user()?->role;
        $allowed = in_array($role, $roles, true) || ($role === 'ADMIN' && in_array('PENGELOLA', $roles, true));
        abort_unless($allowed, 403);

        return $next($request);
    }
}
