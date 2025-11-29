<?php

namespace App\Livewire;

use Livewire\Component;

class UtmUrlGenerator extends Component
{
    public $baseUrl = '';
    public $utmSource = '';
    public $utmMedium = 'social';
    public $utmCampaign = '';
    public $generatedUrl = '';

    public function generateUrl()
    {
        if (!$this->baseUrl) {
            session()->flash('error', 'Base URL is required');
            return;
        }

        $params = [];

        if ($this->utmSource) {
            $params['utm_source'] = $this->utmSource;
        }
        if ($this->utmMedium) {
            $params['utm_medium'] = $this->utmMedium;
        }
        if ($this->utmCampaign) {
            $params['utm_campaign'] = $this->utmCampaign;
        }

        if (empty($params)) {
            session()->flash('error', 'At least one UTM parameter is required');
            return;
        }

        $separator = strpos($this->baseUrl, '?') !== false ? '&' : '?';
        $this->generatedUrl = $this->baseUrl . $separator . http_build_query($params);
    }

    public function copyToClipboard()
    {
        if ($this->generatedUrl) {
            session()->flash('success', 'URL copied to clipboard!');
        }
    }

    public function render()
    {
        return view('livewire.utm-url-generator');
    }
}
