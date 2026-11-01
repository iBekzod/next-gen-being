<?php

namespace App\Livewire;

use App\Models\HelpReport;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

    // Massiv qiymat typed string propertyga yozilganda TypeError bo'ladi (updated* hooklar kech ishlaydi).
    // updating hook tip tekshiruvidan oldin ishlaydi, shuning uchun validatsiya xatosi beramiz.
    public function updating($name, $value): void
    {
        if (is_array($value)) {
            throw ValidationException::withMessages([explode('.', (string) $name)[0] => 'Invalid value.']);
        }
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
