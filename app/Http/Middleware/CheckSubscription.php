<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->isPremium()) {
            return redirect()->route('subscription.plans')
                ->with('message', 'Please subscribe to access premium content.');
        }

        return $next($request);
    }
}
