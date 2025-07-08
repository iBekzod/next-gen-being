@extends('layouts.app')

@section('title', 'My Posts - Dashboard')
@section('description', 'Manage your blog posts')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    My Posts
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Manage and track your published articles
                </p>
            </div>
            @if(auth()->user()->hasAnyRole(['admin', 'content_manager', 'blogger']))
            <a href="{{ route('posts.create') }}" class="inline-flex items-center px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Post
            </a>
            @endif
        </div>

        @livewire('user-dashboard', ['activeTab' => 'posts', 'postsFilter' => $filter])
    </div>
</div>
@endsection
