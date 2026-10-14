<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (! $request->user()) {
            return redirect()->guest(route('login'));
        }

        $allowedRoles = array_map('trim', explode(',', $roles));

        if (! $request->user()->hasAnyRole($allowedRoles)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
