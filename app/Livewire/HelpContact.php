<?php

namespace App\Livewire;

use App\Livewire\Concerns\CoercesTextInput;
use App\Models\HelpReport;
use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

class HelpContact extends Component
{
    use CoercesTextInput;

    // Only the server opens/closes the modal (openModal/closeModal).
    #[Locked]
    public bool $showModal = false;

    // Untyped on purpose: a crafted Livewire request can post arrays, and a typed
    // `string` property would throw a TypeError (500) before validation runs.
    // The updated* hooks below coerce every value back to a string.
    #[Validate('required|string|in:help,report,bug,feature_request')]
    public $type = 'help';

    #[Validate('required|string|min:5|max:255')]
    public $subject = '';

    #[Validate('required|string|min:20|max:2000')]
    public $description = '';

    #[Validate('required|string|in:low,normal,high,urgent')]
    public $priority = 'normal';

    protected $listeners = ['show-help-modal' => 'openModal'];

    public function updatedType($value): void
    {
        $this->type = self::toText($value);
    }

    public function updatedSubject($value): void
    {
        $this->subject = self::toText($value);
    }

    public function updatedDescription($value): void
    {
        $this->description = self::toText($value);
    }

    public function updatedPriority($value): void
    {
        $this->priority = self::toText($value);
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['type', 'subject', 'description', 'priority']);
    }

    public function submit()
    {
        $this->validate();

        $metadata = [
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString(),
        ];

        HelpReport::create([
            'type' => $this->type,
            'subject' => $this->subject,
            'description' => $this->description,
            'priority' => $this->priority,
            'user_id' => Auth::id(),
            'metadata' => $metadata,
        ]);

        $this->closeModal();

        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => 'Your request has been submitted successfully! We\'ll get back to you soon.'
        ]);
    }

    public function render()
    {
        return view('livewire.help-contact');
    }
}

