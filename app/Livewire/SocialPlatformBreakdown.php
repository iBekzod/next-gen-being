<?php

namespace App\Livewire;

use Livewire\Component;

class SocialPlatformBreakdown extends Component
{
    public array $platformData = [
        ['platform' => 'twitter', 'name' => 'Twitter', 'shares' => 0, 'icon' => '𝕏'],
        ['platform' => 'facebook', 'name' => 'Facebook', 'shares' => 0, 'icon' => 'f'],
        ['platform' => 'linkedin', 'name' => 'LinkedIn', 'shares' => 0, 'icon' => 'in'],
        ['platform' => 'whatsapp', 'name' => 'WhatsApp', 'shares' => 0, 'icon' => 'W'],
        ['platform' => 'telegram', 'name' => 'Telegram', 'shares' => 0, 'icon' => '✈️'],
        ['platform' => 'email', 'name' => 'Email', 'shares' => 0, 'icon' => '✉️'],
    ];

    public function mount()
    {
        // Data will be passed from parent component
    }

    public function render()
    {
        return view('livewire.social-platform-breakdown');
    }
}
