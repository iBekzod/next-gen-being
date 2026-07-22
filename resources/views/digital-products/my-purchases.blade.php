@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-950 py-12">
    <div class="container mx-auto px-4 max-w-6xl">
        <!-- Header -->
        <div class="mb-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">My Purchases</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">Download your purchased resources and templates</p>
        </div>

        <!-- Tabs -->
        <div class="flex gap-4 mb-8 border-b border-gray-200 dark:border-slate-700">
            <a href="{{ route('digital-products.my-purchases') }}" class="px-4 py-3 border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 font-semibold">
                All Purchases ({{ auth()->user()->purchases()->count() }})
            </a>
        </div>

        @if($purchases->count())
            <!-- Purchases Table -->
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-slate-900/50 border-b border-gray-200 dark:border-slate-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Product</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Type</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Price Paid</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Downloads</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Purchased</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                            @foreach($purchases as $purchase)
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/40 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0">
                                                @if($purchase->product->thumbnail)
                                                    <img src="{{ Storage::url($purchase->product->thumbnail) }}" alt="{{ $purchase->product->title }}" class="w-10 h-10 rounded object-cover">
                                                @else
                                                    <div class="w-10 h-10 rounded bg-blue-200 dark:bg-blue-900/50 flex items-center justify-center">
                                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4z"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <a href="{{ route('digital-products.show', $purchase->product) }}" class="font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700">
                                                    {{ $purchase->product->title }}
                                                </a>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">License: {{ $purchase->license_key }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block px-3 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 text-sm font-semibold rounded-full">
                                            {{ ucfirst($purchase->product->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                                        @if($purchase->amount > 0)
                                            ${{ number_format($purchase->amount, 2) }}
                                        @else
                                            <span class="text-green-600 dark:text-green-400">FREE</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            <p class="font-semibold text-gray-900 dark:text-white">{{ $purchase->download_count }} / {{ $purchase->download_limit }}</p>
                                            <div class="w-24 h-2 bg-gray-200 dark:bg-slate-700 rounded-full mt-1 overflow-hidden">
                                                <div class="h-full bg-blue-600" style="width: {{ ($purchase->download_count / $purchase->download_limit) * 100 }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $purchase->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($purchase->canDownload())
                                            <form action="{{ route('digital-products.download', $purchase) }}" method="GET" class="inline">
                                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition inline-flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                    </svg>
                                                    Download
                                                </button>
                                            </form>
                                        @else
                                            <button disabled class="px-4 py-2 bg-gray-300 dark:bg-slate-700 text-gray-600 dark:text-gray-400 font-semibold rounded-lg cursor-not-allowed">
                                                Download Limit Reached
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $purchases->links() }}
            </div>

            <!-- Info Box -->
            <div class="mt-8 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                <h3 class="font-semibold text-blue-900 dark:text-blue-200 mb-2">Download Limit Information</h3>
                <p class="text-blue-800 dark:text-blue-300 text-sm">
                    Each purchase includes up to 10 downloads. Once you've downloaded a file, you can redownload it until you reach your download limit. Downloads reset with new purchases.
                </p>
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16 bg-white dark:bg-slate-800 rounded-lg shadow-md">
                <svg class="w-24 h-24 text-gray-300 dark:text-slate-600 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">No Purchases Yet</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-8">Explore our collection of prompts, templates, and tutorials</p>
                <a href="{{ route('digital-products.index') }}" class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    Browse Resources
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
