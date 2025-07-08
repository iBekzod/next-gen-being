@extends('layouts.app')

@section('title', 'Settings - Dashboard')
@section('description', 'Manage your account settings')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Account Settings
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Manage your profile and account preferences
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <nav class="space-y-1">
                    <a href="#profile" class="flex items-center px-3 py-2 text-sm font-medium text-gray-900 rounded-md bg-gray-100 dark:bg-gray-800 dark:text-white">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Profile Information
                    </a>
                    <a href="#password" class="flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Password
                    </a>
                    @if($subscription)
                    <a href="#subscription" class="flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        Subscription
                    </a>
                    @endif
                    <a href="#delete-account" class="flex items-center px-3 py-2 text-sm font-medium text-red-600 rounded-md hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete Account
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-2">
                <div class="space-y-6">
                    <!-- Profile Information -->
                    <div id="profile" class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                Profile Information
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Update your account's profile information and email address.
                            </p>

                            <form action="{{ route('dashboard.settings.update') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-6">
                                @csrf
                                @method('PUT')

                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Name
                                    </label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                           class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Email Address
                                    </label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                                           class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Bio
                                    </label>
                                    <textarea name="bio" id="bio" rows="3"
                                              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">{{ old('bio', $user->bio) }}</textarea>
                                    @error('bio')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="website" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Website
                                    </label>
                                    <input type="url" name="website" id="website" value="{{ old('website', $user->website) }}"
                                           placeholder="https://example.com"
                                           class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                    @error('website')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="twitter" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Twitter Username
                                        </label>
                                        <input type="text" name="twitter" id="twitter" value="{{ old('twitter', $user->twitter) }}"
                                               placeholder="username"
                                               class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                        @error('twitter')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="linkedin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            LinkedIn Username
                                        </label>
                                        <input type="text" name="linkedin" id="linkedin" value="{{ old('linkedin', $user->linkedin) }}"
                                               placeholder="username"
                                               class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                        @error('linkedin')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="avatar" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Profile Photo
                                    </label>
                                    <div class="flex items-center mt-2">
                                        <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}"
                                             alt="{{ $user->name }}"
                                             class="w-12 h-12 rounded-full">
                                        <input type="file" name="avatar" id="avatar" accept="image/*"
                                               class="ml-5 text-sm text-gray-600 dark:text-gray-400">
                                    </div>
                                    @error('avatar')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="px-4 py-2 text-white transition-colors bg-blue-600 rounded-md hover:bg-blue-700">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Update Password -->
                    <div id="password" class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                Update Password
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Ensure your account is using a long, random password to stay secure.
                            </p>

                            <form action="{{ route('dashboard.settings.password') }}" method="POST" class="mt-6 space-y-6">
                                @csrf
                                @method('PUT')

                                <div>
                                    <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Current Password
                                    </label>
                                    <input type="password" name="current_password" id="current_password" required
                                           class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                    @error('current_password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        New Password
                                    </label>
                                    <input type="password" name="password" id="password" required
                                           class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Confirm Password
                                    </label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" required
                                           class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="px-4 py-2 text-white transition-colors bg-blue-600 rounded-md hover:bg-blue-700">
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Subscription -->
                    @if($subscription)
                    <div id="subscription" class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                Subscription
                            </h3>
                            <div class="mt-4">
                                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $subscription->name }} Plan
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Status: <span class="font-medium text-{{ $subscription->isActive() ? 'green' : 'red' }}-600">
                                                    {{ ucfirst($subscription->status) }}
                                                </span>
                                            </p>
                                            @if($subscription->renews_at)
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Renews: {{ $subscription->renews_at->format('M j, Y') }}
                                            </p>
                                            @endif
                                        </div>
                                        <a href="{{ route('subscription.manage') }}" class="px-4 py-2 text-sm font-medium text-blue-600 transition-colors border border-blue-600 rounded-md hover:bg-blue-50 dark:text-blue-400 dark:border-blue-400 dark:hover:bg-blue-900/20">
                                            Manage Subscription
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Delete Account -->
                    <div id="delete-account" class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-red-600 dark:text-red-400">
                                Delete Account
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Once your account is deleted, all of its resources and data will be permanently deleted.
                            </p>

                            <form action="{{ route('dashboard.settings.delete') }}" method="POST" class="mt-6"
                                  onsubmit="return confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')

                                <div>
                                    <label for="delete_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Confirm your password
                                    </label>
                                    <input type="password" name="password" id="delete_password" required
                                           placeholder="Enter your password to confirm"
                                           class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-red-500 focus:border-red-500">
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex justify-end mt-6">
                                    <button type="submit" class="px-4 py-2 text-white transition-colors bg-red-600 rounded-md hover:bg-red-700">
                                        Delete Account
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showNotification('{{ session('success') }}', 'success');
    });
</script>
@endif
@endsection
