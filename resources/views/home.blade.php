@extends('layouts.app')

@section('title', setting('site_name', 'NextGenBeing') . ' - ' . setting('default_meta_title'))
@section('description', setting('default_meta_description'))

@section('content')
<div class="bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800">
    <!-- Hero Section -->
    <div class="relative overflow-hidden">
        <div class="px-4 py-24 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="mb-6 text-4xl font-bold text-gray-900 lg:text-6xl dark:text-white">
                    Welcome to the Future of
                    <span class="text-transparent bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text">
                        Tech Blogging
                    </span>
                </h1>
                <p class="max-w-3xl mx-auto mb-8 text-xl text-gray-600 dark:text-gray-300">
                    Discover cutting-edge tutorials, industry insights, and expert perspectives from leading developers and tech professionals.
                </p>
                <div class="flex flex-col justify-center gap-4 sm:flex-row">
                    <a href="{{ route('posts.index') }}" class="px-8 py-3 font-semibold text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                        Explore Articles
                    </a>
                    @if(setting('enable_subscriptions'))
                    <a href="{{ route('subscription.plans') }}" class="px-8 py-3 font-semibold text-gray-700 transition-colors border border-gray-300 rounded-lg dark:border-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                        View Pricing
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Content -->
    <div class="px-4 py-16 mx-auto max-w-7xl sm:px-6 lg:px-8">
        @livewire('post-list')
    </div>
</div>
@endsection
