@extends('layouts.app')

@section('title', 'Unsubscribed')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
            You've been unsubscribed
        </h1>

        <p class="text-gray-600 dark:text-gray-400 mb-8">
            We're sorry to see you go. You won't receive any more emails from us.
        </p>

        <p class="text-sm text-gray-500 dark:text-gray-500 mb-8">
            Changed your mind? You can always subscribe again from our homepage.
        </p>

        <a href="{{ route('home') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors">
            Go to Homepage
        </a>
    </div>
</div>
@endsection
