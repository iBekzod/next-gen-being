<?php

namespace App\Livewire;

use App\Models\HelpReport;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

class ContactForm extends Component
{
    #[Validate('required|string|min:2|max:100')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|in:general,support,feedback,partnership')]
    public string $subject_type = 'general';

    #[Validate('required|string|min:5|max:255')]
    public string $subject = '';

    #[Validate('required|string|min:20|max:5000')]
    public string $message = '';

    public bool $submitted = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->name = Auth::user()->name;
            $this->email = Auth::user()->email;
        }
    }

    public function updatedName($value): void
    {
        $this->name = is_array($value) ? ($value[0] ?? '') : (string) $value;
    }

    public function updatedEmail($value): void
    {
        $this->email = is_array($value) ? ($value[0] ?? '') : (string) $value;
    }

    public function updatedSubjectType($value): void
    {
        $this->subject_type = is_array($value) ? ($value[0] ?? 'general') : (string) $value;
    }

    public function updatedSubject($value): void
    {
        $this->subject = is_array($value) ? ($value[0] ?? '') : (string) $value;
    }

    public function updatedMessage($value): void
    {
        $this->message = is_array($value) ? ($value[0] ?? '') : (string) $value;
    }

    public function submit(): void
    {
        $this->validate();

        HelpReport::create([
            'type' => 'help',
            'subject' => "[{$this->subject_type}] {$this->subject}",
            'description' => "From: {$this->name} <{$this->email}>\n\n{$this->message}",
            'priority' => 'normal',
            'user_id' => Auth::id(),
            'metadata' => [
                'source' => 'contact_page',
                'name' => $this->name,
                'email' => $this->email,
                'subject_type' => $this->subject_type,
                'user_agent' => request()->userAgent(),
                'ip' => request()->ip(),
                'timestamp' => now()->toISOString(),
            ],
        ]);

        $this->submitted = true;
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'email', 'subject_type', 'subject', 'message', 'submitted']);

        if (Auth::check()) {
            $this->name = Auth::user()->name;
            $this->email = Auth::user()->email;
        }
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
