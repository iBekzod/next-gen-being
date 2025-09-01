@extends('layouts.app')

@section('title', 'Pricing Plans - ' . setting('site_name'))
@section('description', 'Choose the perfect plan for your needs')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="px-4 py-16 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white">
                Choose Your Plan
            </h1>
            <p class="mt-4 text-xl text-gray-600 dark:text-gray-400">
                Get unlimited access to premium content with our flexible pricing options
            </p>
        </div>

        @livewire('subscription-plans')
    </div>
</div>
@endsection
