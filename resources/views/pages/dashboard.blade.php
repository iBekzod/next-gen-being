@extends('layouts.app')

@section('title', 'Dashboard - Next-Gen Being')

@section('content')
<div class="space-y-8">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg p-8">
        <h1 class="text-4xl font-bold mb-2">Welcome back, {{ auth()->user()->name }}!</h1>
        <p class="text-blue-100">Keep creating, keep earning, keep growing</p>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Streaks Widget -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-6">Your Activity</h2>
                @livewire('streak-widget')
            </div>

            <!-- Analytics Dashboard -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-6">Performance Analytics</h2>
                @livewire('analytics-dashboard')
            </div>

            <!-- Content Calendar -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-6">Content Calendar</h2>
                @livewire('content-calendar')
            </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="space-y-6">
            <!-- Leaderboard Widget -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Leaderboards</h2>
                @livewire('leaderboard-widget')
            </div>

            <!-- Creator Tools Quick Links -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Creator Tools</h2>
                <div class="space-y-3">
                    <a href="/dashboard/ideas" class="block p-3 bg-blue-50 hover:bg-blue-100 rounded text-blue-700 font-semibold">
                        💡 Content Ideas
                    </a>
                    <a href="/dashboard/seo" class="block p-3 bg-green-50 hover:bg-green-100 rounded text-green-700 font-semibold">
                        🔍 SEO Analyzer
                    </a>
                    <a href="/dashboard/analytics" class="block p-3 bg-purple-50 hover:bg-purple-100 rounded text-purple-700 font-semibold">
                        📊 Advanced Analytics
                    </a>
                </div>
            </div>

            <!-- Earnings Summary -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-md p-6 border border-green-200">
                <h3 class="text-lg font-bold text-green-900 mb-4">This Month</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-green-700">Tips: </span>
                        <span class="font-bold text-green-900">$1,234</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-green-700">Subscriptions: </span>
                        <span class="font-bold text-green-900">$2,345</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-green-700">Affiliates: </span>
                        <span class="font-bold text-green-900">$567</span>
                    </div>
                    <div class="border-t border-green-300 pt-2 mt-2 flex justify-between">
                        <span class="font-bold text-green-900">Total: </span>
                        <span class="font-bold text-2xl text-green-900">$4,146</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
