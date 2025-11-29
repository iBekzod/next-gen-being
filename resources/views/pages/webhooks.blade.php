@extends('layouts.app')

@section('title', 'Webhooks - Next-Gen Being')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white rounded-lg p-8">
        <h1 class="text-4xl font-bold mb-2">🪝 Webhooks</h1>
        <p class="text-purple-100">Integrate your applications with real-time event notifications</p>
    </div>

    <!-- Info Box -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-blue-900">What are Webhooks?</h3>
        <p class="text-sm text-blue-800 mt-2">
            Webhooks allow external applications to receive real-time notifications when events occur in your Next-Gen Being account.
            When an event is triggered, we'll send a POST request with event details to your configured webhook URL.
        </p>
    </div>

    <!-- Webhook Manager -->
    @livewire('webhook-manager')
</div>
@endsection
