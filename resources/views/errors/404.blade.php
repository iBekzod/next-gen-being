@extends('layouts.app')

@section('title', 'Page Not Found - ' . setting('site_name'))
@section('description', 'The page you are looking for could not be found')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <h1 class="text-9xl font-bold text-gray-200 dark:text-gray-700">404</h1>
        </div>

        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
            Oops! Page not found
        </h2>

        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            The page you're looking for doesn't exist or has been moved.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}"
               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Go to Homepage
            </a>

            <a href="{{ route('posts.index') }}"
               class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Browse Articles
            </a>
        </div>

        <div class="mt-12">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Popular posts you might like:
            </p>
            <div class="grid gap-4 text-left">
                @foreach(\App\Models\Post::published()->popular()->limit(3)->get() as $post)
                <a href="{{ route('posts.show', $post->slug) }}"
                   class="block p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                    <h3 class="font-medium text-gray-900 dark:text-white mb-1">{{ $post->title }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $post->excerpt }}</p>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
