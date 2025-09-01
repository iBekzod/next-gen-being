@extends('layouts.app')

@section('title', 'My Bookmarks - Dashboard')
@section('description', 'View your saved articles')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                My Bookmarks
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Articles you've saved for later reading
            </p>
        </div>

        @livewire('user-dashboard', ['activeTab' => 'bookmarks'])
    </div>
</div>
@endsection
