<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Content Security Policy - Prevents XSS attacks
        $response->header('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' https://fonts.googleapis.com https://fonts.gstatic.com; connect-src 'self' https:; frame-ancestors 'none';");

        // HTTP Strict Transport Security - Forces HTTPS
        if (config('app.env') === 'production') {
            $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Cross-Origin-Opener-Policy - Isolates your site from pop-ups
        $response->header('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');

        // Cross-Origin-Resource-Policy - Protects resources from being accessed by other sites
        $response->header('Cross-Origin-Resource-Policy', 'cross-origin');

        // X-Content-Type-Options - Prevents MIME type sniffing
        $response->header('X-Content-Type-Options', 'nosniff');

        // X-Frame-Options - Prevents clickjacking attacks
        $response->header('X-Frame-Options', 'DENY');

        // X-XSS-Protection - Legacy XSS protection header
        $response->header('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy - Controls referrer information
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy - Controls browser features
        $response->header('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');

        return $response;
    }
}
