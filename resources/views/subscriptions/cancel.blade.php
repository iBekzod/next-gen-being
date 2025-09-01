@extends('layouts.app')

@section('title', 'Subscription Cancelled - ' . setting('site_name'))
@section('description', 'Your subscription process was cancelled')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="px-4 py-16 mx-auto max-w-2xl sm:px-6 lg:px-8">
        <div class="text-center">
            <div class="flex justify-center mb-6">
                <div class="flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full dark:bg-yellow-900">
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>

            <h1 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white">
                Subscription Process Cancelled
            </h1>

            <p class="mb-8 text-lg text-gray-600 dark:text-gray-400">
                Your subscription process was cancelled. No charges were made to your payment method.
            </p>

            <div class="p-6 mb-8 bg-white rounded-lg shadow dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    Not ready to subscribe?
                </h2>
                <p class="mb-4 text-gray-600 dark:text-gray-400">
                    That's okay! You can still enjoy our free content. When you're ready, premium features will be waiting for you.
                </p>
                <ul class="space-y-2 text-sm text-left text-gray-600 dark:text-gray-400">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Continue reading free articles
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Save articles to your bookmarks
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Participate in discussions
                    </li>
                </ul>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:justify-center">
                <a href="{{ route('posts.index') }}" class="px-6 py-3 font-medium text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                    Browse Free Content
                </a>
                <a href="{{ route('subscription.plans') }}" class="px-6 py-3 font-medium text-gray-700 transition-colors border border-gray-300 rounded-lg dark:text-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800">
                    View Plans Again
                </a>
            </div>

            <p class="mt-8 text-sm text-gray-500 dark:text-gray-400">
                Changed your mind? You can go back and complete your subscription anytime.
            </p>
        </div>
    </div>
</div>
@endsection
