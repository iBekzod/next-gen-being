<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guest checkout support for the Lemon Squeezy webhook.
 *
 * A guest who clicks a plan on /pricing goes straight to the hosted checkout,
 * where Lemon Squeezy collects their email. The checkout carries
 * custom data `guest_checkout=1`, but no billable_id, and the Lemon Squeezy
 * package refuses webhooks without one. This middleware (global, but a no-op
 * for any other request) finds or creates the account by the email on the
 * payload and injects billable_id/billable_type/user_id before the package's
 * controller runs, so the subscription links to that account. New accounts
 * get a "set your password" email.
 *
 * The signature is verified here first: an unsigned or forged request is never
 * allowed to create an account (the package middleware then rejects it).
 */
class ProvisionGuestCheckoutBillable
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST') && $request->is(trim(config('lemon-squeezy.path', 'lemon-squeezy'), '/') . '/webhook')) {
            $this->provision($request);
        }

        return $next($request);
    }

    private function provision(Request $request): void
    {
        $secret = (string) config('lemon-squeezy.signing_secret');
        if ($secret === '' || ! hash_equals(hash_hmac('sha256', $request->getContent(), $secret), (string) $request->header('X-Signature'))) {
            return;
        }

        $payload = $request->all();
        $custom = $payload['meta']['custom_data'] ?? null;

        if (! is_array($custom) || empty($custom['guest_checkout']) || isset($custom['billable_id'])) {
            return;
        }

        $attributes = $payload['data']['attributes'] ?? [];
        $email = strtolower(trim((string) ($attributes['user_email'] ?? '')));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Guest checkout webhook without a usable email', ['event' => $payload['meta']['event_name'] ?? null]);

            return;
        }

        $user = User::whereRaw('lower(email) = ?', [$email])->first();
        $created = false;

        if (! $user) {
            $name = trim((string) ($attributes['user_name'] ?? '')) ?: Str::before($email, '@');
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'is_active' => true,
            ]);
            $created = true;
        }

        $custom['billable_id'] = (string) $user->getKey();
        $custom['billable_type'] = $user->getMorphClass();
        $custom['user_id'] = (string) $user->getKey();

        $payload['meta']['custom_data'] = $custom;
        $request->merge(['meta' => $payload['meta']]);

        if ($created) {
            try {
                Password::sendResetLink(['email' => $email]);
            } catch (\Throwable $e) {
                Log::warning('Could not send guest-checkout password link: ' . $e->getMessage());
            }
        }

        Log::info('Guest checkout linked to account', ['user_id' => $user->getKey(), 'created' => $created]);
    }
}
