@extends('layouts.app')

@section('title', 'Discover - Next-Gen Being')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white rounded-lg p-8">
        <h1 class="text-4xl font-bold mb-2">Discover Great Content</h1>
        <p class="text-purple-100">Personalized posts from creators you'll love</p>
    </div>

    <!-- Main Feed -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                <h3 class="text-lg font-bold mb-4">Filters</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Feed Type</label>
                        <select class="w-full border rounded px-3 py-2">
                            <option>All Posts</option>
                            <option>Following</option>
                            <option>Trending</option>
                            <option>Recommended</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Category</label>
                        <select class="w-full border rounded px-3 py-2">
                            <option>All Categories</option>
                            <option>Technology</option>
                            <option>Business</option>
                            <option>Art</option>
                            <option>Science</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Sort By</label>
                        <select class="w-full border rounded px-3 py-2">
                            <option>Latest</option>
                            <option>Most Popular</option>
                            <option>Trending Now</option>
                            <option>Most Commented</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Sidebar Widget: Leaderboard -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <h3 class="text-lg font-bold mb-4">Top Creators</h3>
                <div class="space-y-3">
                    @for($i = 1; $i <= 5; $i++)
                        <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-lg text-blue-600">#{{ $i }}</span>
                                <div>
                                    <p class="font-semibold">Creator {{ $i }}</p>
                                    <p class="text-xs text-gray-500">{{ rand(1000, 100000) }} followers</p>
                                </div>
                            </div>
                            <button class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">
                                Follow
                            </button>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Feed Content -->
        <div class="lg:col-span-3">
            @livewire('discovery-feed')
        </div>
    </div>
</div>
@endsection
