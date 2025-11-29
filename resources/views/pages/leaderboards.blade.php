@extends('layouts.app')

@section('title', 'Leaderboards - Next-Gen Being')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="bg-gradient-to-r from-yellow-600 to-yellow-800 text-white rounded-lg p-8">
        <h1 class="text-4xl font-bold mb-2">🏆 Leaderboards</h1>
        <p class="text-yellow-100">See who's on top and compete for glory</p>
    </div>

    <!-- Leaderboard Selection -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <div class="flex-1">
                <label class="block text-sm font-medium mb-2">Leaderboard Type</label>
                <select class="w-full border rounded px-3 py-2" id="leaderboardType">
                    <option value="creators">Top Creators</option>
                    <option value="readers">Top Readers</option>
                    <option value="engagers">Most Engaged</option>
                    <option value="trending">Trending Posts</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium mb-2">Time Range</label>
                <select class="w-full border rounded px-3 py-2" id="timeRange">
                    <option value="7days">Last 7 Days</option>
                    <option value="30days">Last 30 Days</option>
                    <option value="90days">Last 90 Days</option>
                    <option value="all">All Time</option>
                </select>
            </div>
        </div>

        <!-- Leaderboard Widget -->
        @livewire('leaderboard-widget')
    </div>

    <!-- Leaderboard Details -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Creators Leaderboard -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">👑 Top Creators</h3>
            <div class="space-y-3">
                @for($i = 1; $i <= 5; $i++)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <div class="flex items-center gap-3">
                            <span class="font-bold text-xl text-blue-600">#{{ $i }}</span>
                            <div>
                                <p class="font-semibold">Creator Name</p>
                                <p class="text-xs text-gray-500">{{ rand(1000, 10000) }} followers</p>
                            </div>
                        </div>
                        <span class="font-bold text-blue-600">{{ rand(100, 9999) }} pts</span>
                    </div>
                @endfor
            </div>
        </div>

        <!-- Readers Leaderboard -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">📚 Top Readers</h3>
            <div class="space-y-3">
                @for($i = 1; $i <= 5; $i++)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <div class="flex items-center gap-3">
                            <span class="font-bold text-xl text-green-600">#{{ $i }}</span>
                            <div>
                                <p class="font-semibold">Reader Name</p>
                                <p class="text-xs text-gray-500">{{ rand(100, 1000) }} posts read</p>
                            </div>
                        </div>
                        <span class="font-bold text-green-600">{{ rand(100, 9999) }} pts</span>
                    </div>
                @endfor
            </div>
        </div>

        <!-- Engagers Leaderboard -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">⚡ Most Engaged</h3>
            <div class="space-y-3">
                @for($i = 1; $i <= 5; $i++)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <div class="flex items-center gap-3">
                            <span class="font-bold text-xl text-purple-600">#{{ $i }}</span>
                            <div>
                                <p class="font-semibold">User Name</p>
                                <p class="text-xs text-gray-500">{{ rand(100, 1000) }} interactions</p>
                            </div>
                        </div>
                        <span class="font-bold text-purple-600">{{ rand(100, 9999) }} pts</span>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>
@endsection
