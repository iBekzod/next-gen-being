@extends('layouts.app')

@section('title', 'Earnings - Next-Gen Being')

@section('content')
<div class="space-y-8">
    <div class="bg-gradient-to-r from-green-600 to-green-800 text-white rounded-lg p-8">
        <h1 class="text-4xl font-bold mb-2">💰 Your Earnings</h1>
        <p class="text-green-100">Track your income from all revenue streams</p>
    </div>

    <!-- Earnings Summary Dashboard -->
    @livewire('earnings-summary-dashboard')

    <!-- Payout History -->
    <div class="bg-white rounded-lg shadow-md">
        @livewire('payout-history')
    </div>

    <!-- Revenue Breakdown & Affiliates -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Affiliate Manager -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Affiliate Links</h2>
            @livewire('affiliate-manager')
        </div>

        <!-- Invoice Manager -->
        <div class="bg-white rounded-lg shadow-md">
            @livewire('invoice-manager')
        </div>
    </div>

    <!-- Tax Form Generator -->
    <div class="bg-white rounded-lg shadow-md p-6">
        @livewire('tax-form-generator')
    </div>
</div>
@endsection
