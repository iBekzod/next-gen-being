@extends('layouts.app')

@section('title', 'Newsletter Preferences')

@section('content')
<div class="min-h-screen py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">
            Newsletter Preferences
        </h1>

        @livewire('newsletter-preferences', ['token' => $token])
    </div>
</div>
@endsection
