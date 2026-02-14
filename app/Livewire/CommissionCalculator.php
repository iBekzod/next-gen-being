<?php

namespace App\Livewire;

use Livewire\Component;

class CommissionCalculator extends Component
{
    public string|float $saleAmount = '';
    public int|float $commissionRate = 10;
    public float $estimatedCommission = 0;

    public function updatedSaleAmount()
    {
        $this->calculateCommission();
    }

    public function updatedCommissionRate()
    {
        $this->calculateCommission();
    }

    public function calculateCommission()
    {
        if (!is_numeric($this->saleAmount) || $this->saleAmount < 0) {
            $this->estimatedCommission = 0;
            return;
        }

        $this->estimatedCommission = ($this->saleAmount * $this->commissionRate) / 100;
    }

    public function render()
    {
        return view('livewire.commission-calculator');
    }
}
