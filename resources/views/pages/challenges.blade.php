@extends('layouts.app')

@section('title', 'Challenges - Next-Gen Being')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="bg-gradient-to-r from-red-600 to-red-800 text-white rounded-lg p-8">
        <h1 class="text-4xl font-bold mb-2">🎯 Challenges</h1>
        <p class="text-red-100">Join challenges, earn rewards, prove yourself</p>
    </div>

    <!-- Challenge Filters -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Type</label>
                <select class="w-full border rounded px-3 py-2">
                    <option>All Challenges</option>
                    <option>Reading</option>
                    <option>Writing</option>
                    <option>Engagement</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Difficulty</label>
                <select class="w-full border rounded px-3 py-2">
                    <option>All Difficulties</option>
                    <option>Easy</option>
                    <option>Medium</option>
                    <option>Hard</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Status</label>
                <select class="w-full border rounded px-3 py-2">
                    <option>All Status</option>
                    <option>Active</option>
                    <option>Completed</option>
                    <option>Not Started</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Sort By</label>
                <select class="w-full border rounded px-3 py-2">
                    <option>Recommended</option>
                    <option>Trending</option>
                    <option>Ending Soon</option>
                    <option>Most Joined</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Challenges Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @for($i = 1; $i <= 9; $i++)
            <div>
                @livewire('challenge-card')
            </div>
        @endfor
    </div>

    <!-- Pagination -->
    <div class="flex justify-center gap-2">
        <button class="px-4 py-2 border rounded hover:bg-gray-100">← Previous</button>
        <button class="px-4 py-2 border rounded bg-blue-600 text-white">1</button>
        <button class="px-4 py-2 border rounded hover:bg-gray-100">2</button>
        <button class="px-4 py-2 border rounded hover:bg-gray-100">3</button>
        <button class="px-4 py-2 border rounded hover:bg-gray-100">Next →</button>
    </div>

    <!-- My Challenges -->
    @auth
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-6">My Active Challenges</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @for($i = 1; $i <= 2; $i++)
                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <h3 class="font-bold">Challenge #{{ $i }}</h3>
                        <div class="mt-2">
                            <div class="flex justify-between text-sm mb-2">
                                <span>Progress</span>
                                <span>{{ rand(10, 90) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ rand(10, 90) }}%"></div>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Reward: +{{ rand(10, 100) }} points</p>
                    </div>
                @endfor
            </div>
        </div>
    @endauth
</div>
@endsection
