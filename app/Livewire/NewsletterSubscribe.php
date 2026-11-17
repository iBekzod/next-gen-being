<?php

namespace App\Livewire;

use App\Livewire\Concerns\CoercesTextInput;
use Livewire\Attributes\Locked;
use Livewire\Component;
use App\Services\NewsletterService;
use Illuminate\Support\Facades\Log;

class NewsletterSubscribe extends Component
{
    use CoercesTextInput;

    // Untyped on purpose: a crafted Livewire request can post arrays, and a typed
    // `string` property would throw a TypeError (500) before validation runs.
    // The updated* hooks below coerce every value back to a string.
    public $email = '';
    public $frequency = 'weekly';

    // Server-only state: the client may never write these.
    #[Locked]
    public bool $subscribed = false;
    #[Locked]
    public string $error = '';
    // Untyped: Livewire assigns the matching mount param before mount() runs.
    #[Locked]
    public $compact = false;

    protected $rules = [
        'email' => 'required|email|max:255',
        'frequency' => 'required|in:daily,weekly,monthly',
    ];

    protected $messages = [
        'email.required' => 'Please enter your email address',
        'email.email' => 'Please enter a valid email address',
    ];

    public function mount($compact = false): void
    {
        $this->compact = (bool) self::toText($compact);
    }

    public function updatedEmail($value): void
    {
        $this->email = self::toText($value);
    }

    public function updatedFrequency($value): void
    {
        $this->frequency = self::toText($value);
    }

    public function subscribe()
    {
        $this->validate();

        try {
            $newsletterService = app(NewsletterService::class);

            $userId = auth()->check() ? auth()->id() : null;

            $subscription = $newsletterService->subscribe(
                $this->email,
                $userId,
                $this->frequency
            );

            $this->subscribed = true;
            $this->email = '';

            session()->flash('newsletter_success', 'Please check your email to confirm your subscription!');

            // Dispatch toast notification for better UX
            $this->dispatch('show-notification', [
                'type' => 'success',
                'message' => 'Please check your email to confirm your subscription!',
                'duration' => 5000
            ]);

            Log::info('Newsletter subscription created', [
                'email' => $subscription->email,
                'user_id' => $userId,
            ]);

        } catch (\Exception $e) {
            $this->error = 'Something went wrong. Please try again.';

            // Dispatch error notification
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => $this->error,
                'duration' => 5000
            ]);

            Log::error('Newsletter subscription failed', [
                'email' => $this->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.newsletter-subscribe');
    }
}
