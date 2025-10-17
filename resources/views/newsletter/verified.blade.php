@extends('layouts.app')

@section('title', 'Subscription Confirmed')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
            You're all set!
        </h1>

        <p class="text-gray-600 dark:text-gray-400 mb-8">
            Your newsletter subscription has been confirmed. You'll receive our {{ $subscription->frequency }} digest starting soon.
        </p>

        <div class="space-y-3">
            <a href="{{ route('home') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors">
                Go to Homepage
            </a>

            <a href="{{ route('newsletter.preferences', $subscription->token) }}" class="block w-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 font-semibold px-6 py-3 rounded-lg transition-colors">
                Manage Preferences
            </a>
        </div>
    </div>
</div>
@endsection
