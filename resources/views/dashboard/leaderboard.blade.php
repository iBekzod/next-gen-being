@extends('layouts.app')

@section('title', 'Learning Leaderboard - ' . setting('site_name'))
@section('description', 'View the top learners and your learning progress')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-12 mx-auto max-w-7xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Learning Leaderboard 🏆</h1>
                <p class="mt-2 text-slate-300">See how you rank among all learners</p>
            </div>
            @if($userPosition)
            <div class="text-right">
                <div class="text-5xl font-bold text-yellow-400 mb-2">#{{ $userPosition }}</div>
                <p class="text-slate-300">Your Rank</p>
            </div>
            @endif
        </div>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-7xl">
        <!-- Learning Progress Card -->
        <div class="mb-12">
            <x-learning-progress-card :user="$currentUser" />
        </div>

        <!-- Full Leaderboard -->
        <div class="mb-12">
            <x-leaderboard :learners="$topLearners" :current-user="$currentUser" :limit="50" />
        </div>

        <!-- Call to Action -->
        @if(!$currentUser || !auth()->check())
        <div class="rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-900 dark:to-indigo-900 p-8 text-center text-white">
            <h3 class="text-2xl font-bold mb-4">Join the Learning Community</h3>
            <p class="text-blue-100 mb-6">Start learning and climb the leaderboard to unlock achievements and badges!</p>
            <a href="{{ route('tutorials.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition">
                Explore Tutorials
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
        @endif
    </div>
</section>
@endsection
