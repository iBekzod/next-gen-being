<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized access');
        }

        $user = auth()->user();

        // Check if user has admin role or is_admin flag
        if ($user->hasRole('admin') || $user->is_admin ?? false) {
            return $next($request);
        }

        abort(403, 'Admin access required');
    }
}
