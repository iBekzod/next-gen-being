@extends('layouts.dashboard')

@section('title', 'Collaboration - ' . $post->title)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="mb-6">
        <a href="{{ route('posts.edit', $post) }}" class="text-blue-600 hover:text-blue-700 flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Back to Post</span>
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Collaboration Panel</h1>

        <div class="space-y-6">
            {{-- Post Info --}}
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ $post->title }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">By {{ $post->author->name }}</p>
            </div>

            {{-- Collaboration Stats --}}
            @if($stats)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total_collaborators'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Active Collaborators</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['total_activities'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Activities</div>
                </div>
                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['versions_count'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Versions</div>
                </div>
            </div>
            @endif

            {{-- Collaborators List --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Collaborators</h3>
                @if($post->activeCollaborators()->count() > 0)
                    <div class="space-y-3">
                        @foreach($post->activeCollaborators as $collaborator)
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $collaborator->user->name }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ ucfirst($collaborator->role) }}</div>
                                </div>
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Active
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600 dark:text-gray-400">No active collaborators for this post.</p>
                @endif
            </div>

            {{-- Action Buttons --}}
            <div class="flex space-x-4 pt-4">
                <a href="{{ route('collaboration.history', $post) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    View History
                </a>
                @if($canManage)
                    <a href="{{ route('collaboration.export', $post) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Export Report
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
