@extends('layouts.dashboard')

@section('title', 'Collaboration History - ' . $post->title)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="mb-6">
        <a href="{{ route('collaboration.show', $post) }}" class="text-blue-600 hover:text-blue-700 flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Back to Collaboration Panel</span>
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Collaboration History</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $post->title }}</p>
            </div>
            <a href="{{ route('collaboration.export', $post) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                Export CSV
            </a>
        </div>

        @if($history && count($history) > 0)
            <div class="space-y-6">
                @foreach($history as $item)
                    <div class="border-l-4 {{ $item['type'] === 'edit' ? 'border-blue-500' : ($item['type'] === 'comment' ? 'border-green-500' : 'border-gray-500') }} pl-4 py-2">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $item['user'] ?? 'Unknown User' }}</span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $item['type'] === 'edit' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : ($item['type'] === 'comment' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200') }}">
                                        {{ ucfirst($item['type']) }}
                                    </span>
                                </div>
                                <p class="text-gray-700 dark:text-gray-300">{{ $item['description'] ?? 'No description' }}</p>
                                @if(isset($item['details']))
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $item['details'] }}</p>
                                @endif
                            </div>
                            <time class="text-sm text-gray-500 dark:text-gray-400 ml-4">
                                {{ $item['timestamp'] ?? 'Unknown time' }}
                            </time>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No collaboration history</h3>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Activity history will appear here once collaborators start working on this post.</p>
            </div>
        @endif
    </div>
</div>
@endsection
