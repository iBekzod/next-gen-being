@extends('layouts.app')

@section('title', 'Dashboard - ' . setting('site_name'))
@section('description', 'Manage your account, posts, and settings')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Welcome back, {{ auth()->user()->name }}!
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Here's an overview of your account activity
            </p>
        </div>

        @livewire('user-dashboard')
    </div>
</div>
@endsection
