<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\NewsletterService;
use Illuminate\Support\Facades\Log;

class NewsletterSubscribe extends Component
{
    public $email = '';
    public $frequency = 'weekly';
    public $subscribed = false;
    public $error = '';
    public $compact = false;

    protected $rules = [
        'email' => 'required|email|max:255',
        'frequency' => 'required|in:daily,weekly,monthly',
    ];

    protected $messages = [
        'email.required' => 'Please enter your email address',
        'email.email' => 'Please enter a valid email address',
    ];

    public function mount($compact = false)
    {
        $this->compact = $compact;
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

            Log::info('Newsletter subscription created', [
                'email' => $subscription->email,
                'user_id' => $userId,
            ]);

        } catch (\Exception $e) {
            $this->error = 'Something went wrong. Please try again.';

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
