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

        // Use the Symfony HeaderBag (`headers->set`) rather than the Laravel-only
        // fluent `$response->header()`. The latter does not exist on
        // BinaryFileResponse / StreamedResponse / plain Symfony responses, which
        // made every file download (and some webhook/error responses) 500.
        $headers = $response->headers;

        // Content Security Policy - Prevents XSS attacks
        // Relaxed for Google AdSense monetization - allows all HTTPS external resources
        $headers->set('Content-Security-Policy', "default-src 'self' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' https:; connect-src 'self' https:; frame-src https:; frame-ancestors 'none';");

        // HTTP Strict Transport Security - Forces HTTPS
        if (config('app.env') === 'production') {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Cross-Origin-Opener-Policy - Isolates your site from pop-ups
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');

        // Cross-Origin-Resource-Policy - Protects resources from being accessed by other sites
        $headers->set('Cross-Origin-Resource-Policy', 'cross-origin');

        // X-Content-Type-Options - Prevents MIME type sniffing
        $headers->set('X-Content-Type-Options', 'nosniff');

        // X-Frame-Options - Prevents clickjacking attacks
        $headers->set('X-Frame-Options', 'DENY');

        // X-XSS-Protection - Legacy XSS protection header
        $headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy - Controls referrer information
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy - Controls browser features
        $headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');

        return $response;
    }
}
