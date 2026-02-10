@extends('layouts.dashboard')

@section('title', 'Review Post - ' . $post->title)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <div class="mb-6">
        <a href="{{ route('admin.moderation.index') }}" class="text-blue-600 hover:text-blue-700 flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Back to Moderation Queue</span>
        </a>
    </div>

    {{-- Post Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 mb-6">
        <div class="flex items-start justify-between mb-6">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">{{ $post->title }}</h1>

                <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>{{ $post->author->name }}</span>
                    </div>
                    <span>•</span>
                    <span>{{ $post->created_at->format('M d, Y') }}</span>
                    <span>•</span>
                    <span>{{ $post->category->name ?? 'Uncategorized' }}</span>
                </div>
            </div>

            <div class="flex flex-col items-end space-y-2">
                @if($post->moderation_status === 'pending')
                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                        Pending Review
                    </span>
                @elseif($post->moderation_status === 'approved')
                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        Approved
                    </span>
                @else
                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                        Rejected
                    </span>
                @endif
            </div>
        </div>

        @if($post->featured_image)
            <div class="mb-6">
                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-64 object-cover rounded-lg">
            </div>
        @endif

        <div class="prose dark:prose-invert max-w-none mb-6">
            <div class="text-lg text-gray-700 dark:text-gray-300 mb-4">
                {{ $post->excerpt }}
            </div>
            <div class="text-gray-800 dark:text-gray-200">
                {!! $post->content !!}
            </div>
        </div>

        @if($post->tags->count() > 0)
            <div class="flex flex-wrap gap-2 mb-6">
                @foreach($post->tags as $tag)
                    <span class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full">
                        {{ $tag->name }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    {{-- AI Moderation Check --}}
    @if($post->ai_moderation_check)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">AI Moderation Analysis</h3>
            <div class="space-y-3">
                @if(isset($post->ai_moderation_check['is_appropriate']))
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 dark:text-gray-300">Content Appropriate:</span>
                        <span class="{{ $post->ai_moderation_check['is_appropriate'] ? 'text-green-600' : 'text-red-600' }} font-medium">
                            {{ $post->ai_moderation_check['is_appropriate'] ? 'Yes' : 'No' }}
                        </span>
                    </div>
                @endif
                @if(isset($post->ai_moderation_check['confidence_score']))
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 dark:text-gray-300">Confidence Score:</span>
                        <span class="font-medium">{{ round($post->ai_moderation_check['confidence_score'] * 100) }}%</span>
                    </div>
                @endif
                @if(isset($post->ai_moderation_check['reason']))
                    <div>
                        <span class="text-gray-700 dark:text-gray-300 font-medium">Analysis:</span>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $post->ai_moderation_check['reason'] }}</p>
                    </div>
                @endif
            </div>

            <form action="{{ route('admin.moderation.recheck', $post) }}" method="POST" class="mt-4">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-700 border border-blue-600 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                    Re-run AI Check
                </button>
            </form>
        </div>
    @endif

    {{-- Moderation Actions --}}
    @if($post->moderation_status === 'pending')
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Moderation Actions</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Approve Form --}}
                <form action="{{ route('admin.moderation.approve', $post) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="approve_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Approval Notes (Optional)
                        </label>
                        <textarea name="notes" id="approve_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white" placeholder="Add any notes about this approval..."></textarea>
                    </div>
                    <button type="submit" class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                        Approve & Publish
                    </button>
                </form>

                {{-- Reject Form --}}
                <form action="{{ route('admin.moderation.reject', $post) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="reject_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Rejection Reason (Required)
                        </label>
                        <textarea name="reason" id="reject_reason" rows="3" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white" placeholder="Explain why this content is being rejected..."></textarea>
                    </div>
                    <button type="submit" class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
                        Reject Post
                    </button>
                </form>
            </div>
        </div>
    @else
        {{-- Show moderation history --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Moderation History</h3>
            <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                @if($post->moderator)
                    <p><strong>Moderated by:</strong> {{ $post->moderator->name }}</p>
                @endif
                @if($post->moderated_at)
                    <p><strong>Moderated at:</strong> {{ $post->moderated_at->format('M d, Y g:i A') }}</p>
                @endif
                @if($post->moderation_notes)
                    <p><strong>Notes:</strong> {{ $post->moderation_notes }}</p>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
