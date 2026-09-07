<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
use App\Models\NewsletterSubscription;
use App\Services\NewsletterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Bepul prompt magnitining email darvozasi (spec §7).
 *
 * Ataylab hisob TALAB QILMAYDI: begona odam bilan eng kuchli aktiv
 * o'rtasida ro'yxatdan o'tish devori bo'lsa, magnitning ma'nosi qolmaydi.
 * Mavjud double-opt-in tokeni yagona ishqalanish nuqtasi bo'lib qoladi.
 *
 * Bu yerda ProductPurchase YARATILMAYDI: email yig'ish sotuv emas, va
 * ikkisini aralashtirish sales_count ni buzadi.
 */
class PromptGateController extends Controller
{
    /** Formada chastota tanlanmaganda ishlatiladigan qiymat. */
    private const DEFAULT_FREQUENCY = 'weekly';

    public function __construct(private readonly NewsletterService $newsletter)
    {
    }

    public function request(Request $request, MarketplaceListing $listing): RedirectResponse
    {
        abort_unless($listing->status === 'published', 404);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            // Spec §7: obunachining o'zi chastotani tanlaydi. Standart —
            // HAFTALIK: kunlik digest faqat oxirgi 24 soatda post chiqqanda
            // yuboriladi, shuning uchun "daily" ni majburlash yangi
            // obunachilarni onboarding tugagach jimlikka mahkum qilardi.
            'frequency' => 'nullable|in:daily,weekly',
        ]);

        $frequency = $validated['frequency'] ?? self::DEFAULT_FREQUENCY;

        try {
            // DIQQAT: NewsletterService::subscribe() updateOrCreate ishlatadi va
            // mavjud obunachining `frequency` hamda `preferences` maydonlarini
            // QAYTA YOZADI. Uni shartsiz chaqirsak, haftalik obunachi prompt
            // so'raganda uning xat chastotasi so'ramasdan kunlikka o'zgarardi —
            // bu foydalanuvchi sozlamasini jimgina buzish bo'ladi.
            // Shuning uchun mavjud obunachi uchun faqat preferences'ga
            // qo'shamiz (updatePreferences() birlashtiradi), chastotaga tegmaymiz.
            $existing = NewsletterSubscription::where('email', $validated['email'])->first();

            if ($existing) {
                $existing->updatePreferences(['pending_prompt_listing' => $listing->slug]);
                $subscription = $existing->fresh();

                if (! $subscription->verified_at) {
                    $this->newsletter->sendVerificationEmail($subscription);
                }
            } else {
                $subscription = $this->newsletter->subscribe(
                    $validated['email'],
                    null,
                    $frequency,
                    ['pending_prompt_listing' => $listing->slug]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Prompt gate subscribe failed', [
                'listing' => $listing->slug,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Emailni qabul qilib bo\'lmadi. Birozdan keyin urinib ko\'ring.');
        }

        if ($subscription->verified_at) {
            return redirect()->to($this->newsletter->promptDownloadUrl($subscription, $listing));
        }

        return back()->with('success', 'Tasdiqlash havolasini emailingizga yubordik — bosing va prompt sizniki.');
    }

    public function download(MarketplaceListing $listing, NewsletterSubscription $subscription): StreamedResponse
    {
        abort_unless($listing->status === 'published', 404);

        $product = $listing->tiers()
            ->where('tier', 'prompt')
            ->whereNotNull('file_path')
            ->first();

        abort_if($product === null, 404, 'Bu mahsulot uchun prompt hali tayyor emas.');
        abort_unless(Storage::disk('private')->exists($product->file_path), 404);

        Log::info('Prompt magnet delivered', [
            'listing' => $listing->slug,
            'subscription_id' => $subscription->id,
        ]);

        return Storage::disk('private')->download(
            $product->file_path,
            $listing->slug . '-prompt.md'
        );
    }
}
