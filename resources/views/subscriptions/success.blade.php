@extends('layouts.app')

@section('title', 'Subscription Successful - ' . setting('site_name'))
@section('description', 'Thank you for subscribing!')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="px-4 py-16 mx-auto max-w-2xl sm:px-6 lg:px-8">
        <div class="text-center">
            <div class="flex justify-center mb-6">
                <div class="flex items-center justify-center w-16 h-16 bg-green-100 rounded-full dark:bg-green-900">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            <h1 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white">
                Welcome to Premium!
            </h1>

            <p class="mb-8 text-lg text-gray-600 dark:text-gray-400">
                Your subscription has been activated successfully. You now have access to all premium content and features.
            </p>

            <div class="p-6 mb-8 bg-white rounded-lg shadow dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    What's included in your subscription:
                </h2>
                <ul class="space-y-3 text-left">
                    <li class="flex items-start">
                        <svg class="flex-shrink-0 w-5 h-5 mt-0.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-3 text-gray-700 dark:text-gray-300">Unlimited access to all premium articles</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="flex-shrink-0 w-5 h-5 mt-0.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-3 text-gray-700 dark:text-gray-300">Ad-free reading experience</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="flex-shrink-0 w-5 h-5 mt-0.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-3 text-gray-700 dark:text-gray-300">Early access to new content</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="flex-shrink-0 w-5 h-5 mt-0.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-3 text-gray-700 dark:text-gray-300">Priority support</span>
                    </li>
                </ul>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:justify-center">
                <a href="{{ route('posts.index') }}" class="px-6 py-3 font-medium text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                    Start Reading Premium Content
                </a>
                <a href="{{ route('subscription.manage') }}" class="px-6 py-3 font-medium text-gray-700 transition-colors border border-gray-300 rounded-lg dark:text-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800">
                    Manage Subscription
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
