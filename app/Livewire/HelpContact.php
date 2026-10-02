<?php

namespace App\Livewire;

use App\Models\HelpReport;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

class HelpContact extends Component
{
    public bool $showModal = false;

    // Tiplar ataylab yozilmagan: mijoz massiv yuborsa Livewire tipli
    // propertyga o'rnatishda TypeError tashlardi (updated* hooklar undan keyin
    // ishlaydi). Endi updated* hook normallashtiradi, 'string' qoidasi tekshiradi.
    #[Validate('required|string|in:help,report,bug,feature_request')]
    public $type = 'help';

    #[Validate('required|string|min:5|max:255')]
    public $subject = '';

    #[Validate('required|string|min:20|max:2000')]
    public $description = '';

    #[Validate('required|string|in:low,normal,high,urgent')]
    public $priority = 'normal';

    protected $listeners = ['show-help-modal' => 'openModal'];

    public function updatedType($value)
    {
        $this->type = is_array($value) ? ($value[0] ?? 'help') : $value;
    }

    public function updatedSubject($value)
    {
        $this->subject = is_array($value) ? ($value[0] ?? '') : $value;
    }

    public function updatedDescription($value)
    {
        $this->description = is_array($value) ? ($value[0] ?? '') : $value;
    }

    public function updatedPriority($value)
    {
        $this->priority = is_array($value) ? ($value[0] ?? 'normal') : $value;
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

