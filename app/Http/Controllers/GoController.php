<?php

namespace App\Http\Controllers;

use App\Models\OutboundClick;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * /go/<slug> - the single exit point for every partner link on the site.
 *
 * Posts link to /go/elevenlabs, never to the raw referral URL, so a program
 * can be swapped (or a dead one pointed back at the vendor's homepage) by
 * changing AFFILIATE_*_URL in .env - no post content has to be rewritten.
 * Each click is logged in outbound_clicks.
 *
 * 302 on purpose: a 301 would be cached by browsers and pin the old target.
 */
class GoController extends Controller
{
    private const BOT_UA = '/bot|crawl|spider|slurp|facebookexternalhit|telegrambot|whatsapp|preview|headless|curl|wget|python-requests|httpclient/i';

    public function __invoke(Request $request, string $slug): RedirectResponse
    {
        $link = config("affiliate.links.{$slug}");
        if (! is_array($link)) {
            abort(404);
        }

        $affiliateUrl = trim((string) ($link['url'] ?? ''));
        $target = $affiliateUrl !== '' ? $affiliateUrl : trim((string) ($link['homepage'] ?? ''));
        if ($target === '') {
            abort(404);
        }

        $ua = (string) $request->userAgent();
        if ($ua !== '' && ! preg_match(self::BOT_UA, $ua)) {
            $this->log($request, $slug, $affiliateUrl !== '', $ua);
        }

        return redirect()->away($target, 302)->withHeaders([
            'X-Robots-Tag' => 'noindex, nofollow',
            'Cache-Control' => 'no-store',
            'Referrer-Policy' => 'no-referrer-when-downgrade',
        ]);
    }

    private function log(Request $request, string $slug, bool $isAffiliate, string $ua): void
    {
        try {
            $referrer = (string) $request->headers->get('referer', '');
            OutboundClick::create([
                'slug' => $slug,
                'post_id' => $this->postIdFrom($request, $referrer),
                'is_affiliate' => $isAffiliate,
                'referrer' => $referrer !== '' ? mb_substr($referrer, 0, 500) : null,
                'ip_hash' => hash('sha256', $request->ip() . '|' . config('app.key')),
                'user_agent' => mb_substr($ua, 0, 500),
                'lang' => mb_substr((string) $request->getPreferredLanguage(), 0, 8) ?: null,
            ]);
        } catch (\Throwable $e) {
            // Never let logging break the redirect - the click still goes through.
            Log::warning('outbound click log failed', ['slug' => $slug, 'error' => $e->getMessage()]);
        }
    }

    private function postIdFrom(Request $request, string $referrer): ?int
    {
        if ($request->filled('p') && ctype_digit((string) $request->query('p'))) {
            return (int) $request->query('p');
        }

        $path = (string) parse_url($referrer, PHP_URL_PATH);
        if (preg_match('#^/posts/([a-z0-9-]+)/?$#i', $path, $m)) {
            return Post::where('slug', $m[1])->value('id');
        }

        return null;
    }
}
