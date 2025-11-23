<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Post;
use App\Services\TipService;
use Illuminate\Support\Facades\Log;

class TipButton extends Component
{
    public User $recipient;
    public ?Post $post = null;
    public TipService $tipService;

    public $showModal = false;
    public $tipAmount = 5;
    public $message = '';
    public $isAnonymous = false;
    public $isProcessing = false;

    public function mount(User $recipient, ?Post $post = null)
    {
        $this->recipient = $recipient;
        $this->post = $post;
        $this->tipService = app(TipService::class);
    }

    public function submitTip()
    {
        $this->validate([
            'tipAmount' => 'required|numeric|min:1|max:1000',
            'message' => 'nullable|string|max:500',
        ]);

        $this->isProcessing = true;

        try {
            $result = $this->tipService->initiateTip(
                fromUser: auth()->user(),
                toUser: $this->recipient,
                amount: $this->tipAmount,
                post: $this->post,
                message: $this->message,
                isAnonymous: $this->isAnonymous
            );

            if ($result['success']) {
                $this->dispatch('tipSuccess', [
                    'amount' => $this->tipAmount,
                    'recipient' => $this->recipient->name,
                ]);
                $this->showModal = false;
                $this->resetForm();
            } else {
                $this->addError('tipAmount', $result['message'] ?? 'Failed to send tip');
            }
        } catch (\Exception $e) {
            Log::error('Tip submission error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            $this->addError('tipAmount', 'An error occurred. Please try again.');
        } finally {
            $this->isProcessing = false;
        }
    }

    public function resetForm()
    {
        $this->tipAmount = 5;
        $this->message = '';
        $this->isAnonymous = false;
    }

    public function render()
    {
        return view('livewire.tip-button');
    }
}
