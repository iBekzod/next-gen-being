<?php

namespace App\Livewire;

use App\Models\HelpReport;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

class HelpContact extends Component
{
    public bool $showModal = false;

    #[Validate('required|in:help,report,bug,feature_request')]
    public string $type = 'help';

    #[Validate('required|min:5|max:255')]
    public string $subject = '';

    #[Validate('required|min:20|max:2000')]
    public string $description = '';

    #[Validate('required|in:low,normal,high,urgent')]
    public string $priority = 'normal';

    protected $listeners = ['show-help-modal' => 'showModal'];

    public function updatedPriority($value)
    {
        $this->priority = is_array($value) ? ($value[0] ?? 'normal') : $value;
    }

    public function showModal()
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

