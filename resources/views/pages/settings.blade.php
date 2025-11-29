@extends('layouts.app')

@section('title', 'Settings - Next-Gen Being')

@section('content')
<div class="space-y-8">
    <div class="bg-gradient-to-r from-gray-600 to-gray-800 text-white rounded-lg p-8">
        <h1 class="text-4xl font-bold mb-2">Settings</h1>
        <p class="text-gray-100">Manage your account and preferences</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-4 sticky top-4">
                <nav class="space-y-2">
                    <a href="#profile" class="block p-3 bg-blue-50 text-blue-700 rounded font-semibold">Profile</a>
                    <a href="#preferences" class="block p-3 text-gray-700 hover:bg-gray-100 rounded">Preferences</a>
                    <a href="#notifications" class="block p-3 text-gray-700 hover:bg-gray-100 rounded">Notifications</a>
                </nav>
            </div>
        </div>

        <div class="lg:col-span-3 space-y-6">
            <div id="preferences" class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-6">Reader Preferences</h2>
                @livewire('reader-preference-ui')
            </div>
        </div>
    </div>
</div>
@endsection
