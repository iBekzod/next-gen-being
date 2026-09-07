@extends('layouts.app')

@section('title', 'Confirmation Link Invalid')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <svg class="mx-auto h-16 w-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
            Link no longer valid
        </h1>

        <p class="text-gray-600 dark:text-gray-400 mb-8">
            This confirmation link is invalid or has already been used. If you already confirmed your subscription, you don't need to do anything else.
        </p>

        <div class="space-y-3">
            <a href="{{ route('home') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors">
                Go to Homepage
            </a>
        </div>
    </div>
</div>
@endsection
