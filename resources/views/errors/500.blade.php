@extends('layouts.app')

@section('title', 'Server Error - ' . setting('site_name'))
@section('description', 'Something went wrong on our servers')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <h1 class="text-9xl font-bold text-gray-200 dark:text-gray-700">500</h1>
        </div>

        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
            Server Error
        </h2>

        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            Something went wrong on our end. We're working to fix it as soon as possible.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button onclick="window.location.reload()"
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Try Again
            </button>

            <a href="{{ route('home') }}"
               class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Go to Homepage
            </a>
        </div>

        <div class="mt-12">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                If this problem persists, please contact our support team:
            </p>
            <button @click="$dispatch('show-help-modal')"
                    class="inline-flex items-center text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                Get Help
            </button>
        </div>
    </div>
</div>
@endsection
