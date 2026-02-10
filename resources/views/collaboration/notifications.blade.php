@extends('layouts.dashboard')

@section('title', 'Collaboration Notifications')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Collaboration Notifications</h1>

    {{-- Pending Invitations --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Pending Invitations</h2>

        @if($pendingInvitations && count($pendingInvitations) > 0)
            <div class="space-y-4">
                @foreach($pendingInvitations as $invitation)
                    <div class="flex items-center justify-between p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900 dark:text-white">{{ $invitation->post->title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Invited by {{ $invitation->invited_by->name }} as {{ ucfirst($invitation->role) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                Expires: {{ $invitation->expires_at?->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex space-x-2 ml-4">
                            <a href="{{ route('collaboration.invitation.accept', ['token' => $invitation->token]) }}"
                               class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition">
                                Accept
                            </a>
                            <form action="{{ route('collaboration.invitation.decline', $invitation) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg transition">
                                    Decline
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600 dark:text-gray-400">You have no pending collaboration invitations.</p>
        @endif
    </div>

    {{-- Active Collaborations --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Active Collaborations</h2>

        @if($activeCollaborations && count($activeCollaborations) > 0)
            <div class="space-y-3">
                @foreach($activeCollaborations as $collaboration)
                    <a href="{{ route('posts.edit', $collaboration->post) }}"
                       class="block p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">{{ $collaboration->post->title }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Role: {{ ucfirst($collaboration->role) }} •
                                    By {{ $collaboration->post->author->name }}
                                </p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-gray-600 dark:text-gray-400">You are not currently collaborating on any posts.</p>
        @endif
    </div>
</div>
@endsection
