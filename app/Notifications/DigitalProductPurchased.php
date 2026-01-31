<?php

namespace App\Notifications;

use App\Models\ProductPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DigitalProductPurchased extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ProductPurchase $purchase
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $product = $this->purchase->product;

        return (new MailMessage)
            ->subject("Your Purchase: {$product->title}")
            ->greeting("Thank you for your purchase!")
            ->line("You've successfully purchased: **{$product->title}**")
            ->line("Price: " . ($product->is_free ? "FREE" : "$" . number_format($product->price, 2)))
            ->line("")
            ->line("Your License Key:")
            ->line("```\n{$this->purchase->license_key}\n```")
            ->line("")
            ->line("**Download Information:**")
            ->line("- Downloads Remaining: {$this->purchase->download_limit}")
            ->line("- Maximum Downloads: {$this->purchase->download_limit}")
            ->line("")
            ->action('Download Now', route('digital-products.download-index'))
            ->line('You can download this resource up to ' . $this->purchase->download_limit . ' times.')
            ->line('Thank you for supporting our creators!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'purchase_id' => $this->purchase->id,
            'product_id' => $this->purchase->product->id,
            'product_title' => $this->purchase->product->title,
            'amount' => $this->purchase->amount,
        ];
    }
}
