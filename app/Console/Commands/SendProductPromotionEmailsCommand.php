<?php

namespace App\Console\Commands;

use App\Models\DigitalProduct;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendProductPromotionEmailsCommand extends Command
{
    protected $signature = 'revenue:send-product-emails {--segment=free}';
    protected $description = 'Send automated product promotion emails to subscribers';

    public function handle(): int
    {
        $segment = $this->option('segment'); // free, basic, pro

        // Get users based on segment
        $users = $this->getUsersBySegment($segment);

        if ($users->isEmpty()) {
            $this->info('No users found in ' . $segment . ' segment');
            return self::SUCCESS;
        }

        // Get featured products
        $featuredProducts = DigitalProduct::published()
            ->orderByDesc('purchases_count')
            ->limit(3)
            ->get();

        if ($featuredProducts->isEmpty()) {
            $this->info('No products available for promotion');
            return self::SUCCESS;
        }

        $sentCount = 0;

        foreach ($users as $user) {
            if ($user->email) {
                try {
                    // Send promotional email
                    Mail::send('emails.product-promotion', [
                        'user' => $user,
                        'products' => $featuredProducts,
                        'segment' => $segment,
                    ], function ($message) use ($user) {
                        $message->to($user->email)
                            ->subject('🎯 New AI Prompts & Templates Available');
                    });

                    $sentCount++;
                } catch (\Exception $e) {
                    $this->warn("Failed to send email to {$user->email}: {$e->getMessage()}");
                }
            }
        }

        $this->info("📧 Promotional emails sent to {$sentCount} users");
        return self::SUCCESS;
    }

    /**
     * Get users by subscription segment
     */
    private function getUsersBySegment(string $segment)
    {
        return match($segment) {
            'free' => User::doesntHave('activeSubscription')
                ->where('email_notifications', true)
                ->inRandomOrder()
                ->limit(100)
                ->get(),

            'basic' => User::whereHas('activeSubscription', function ($q) {
                $q->whereIn('plan', ['basic', 'Basic']);
            })
                ->where('email_notifications', true)
                ->inRandomOrder()
                ->limit(100)
                ->get(),

            'pro' => User::whereHas('activeSubscription', function ($q) {
                $q->whereIn('plan', ['pro', 'Pro']);
            })
                ->where('email_notifications', true)
                ->limit(100)
                ->get(),

            default => User::where('email_notifications', true)
                ->inRandomOrder()
                ->limit(50)
                ->get(),
        };
    }
}
