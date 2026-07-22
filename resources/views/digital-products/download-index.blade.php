@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-950 py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Your Downloads</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">Access all your purchased resources in one place</p>
        </div>

        @if($purchases->count())
            <!-- Downloads Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                @foreach($purchases as $purchase)
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                            <h3 class="text-xl font-bold text-white">{{ $purchase->product->title }}</h3>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <!-- Type Badge -->
                            <div class="mb-4">
                                <span class="inline-block px-3 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 text-sm font-semibold rounded-full">
                                    {{ ucfirst($purchase->product->type) }}
                                </span>
                            </div>

                            <!-- Description -->
                            <p class="text-gray-700 dark:text-gray-300 mb-4 text-sm">{{ $purchase->product->short_description }}</p>

                            <!-- Download Info -->
                            <div class="bg-gray-50 dark:bg-slate-900/50 rounded-lg p-4 mb-6">
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">Downloads Used</span>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $purchase->download_count }}/{{ $purchase->download_limit }}</span>
                                    </div>
                                    <div class="w-full h-2 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-600 transition-all" style="width: {{ ($purchase->download_count / $purchase->download_limit) * 100 }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- License Key -->
                            <div class="mb-6">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">License Key</p>
                                <div class="bg-gray-100 dark:bg-slate-700 rounded px-4 py-3 font-mono text-sm text-gray-900 dark:text-gray-100 break-all">
                                    {{ $purchase->license_key }}
                                </div>
                            </div>

                            <!-- Download Button & Purchased Date -->
                            <div class="flex flex-col gap-3">
                                @if($purchase->canDownload())
                                    <form action="{{ route('digital-products.download', $purchase) }}" method="GET">
                                        <button type="submit" class="w-full py-3 px-4 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                            Download
                                        </button>
                                    </form>
                                @else
                                    <button disabled class="w-full py-3 px-4 bg-gray-300 dark:bg-slate-700 text-gray-600 dark:text-gray-400 font-bold rounded-lg cursor-not-allowed">
                                        Download Limit Reached
                                    </button>
                                @endif

                                <p class="text-xs text-gray-600 dark:text-gray-400 text-center">
                                    Purchased: {{ $purchase->created_at->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center mb-12">
                {{ $purchases->links() }}
            </div>

            <!-- Info -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 text-center">
                <h3 class="font-bold text-blue-900 dark:text-blue-200 mb-2">Need Help?</h3>
                <p class="text-blue-800 dark:text-blue-300 text-sm mb-4">
                    Each purchase allows 10 downloads. You can redownload files until you reach your limit.
                </p>
                <a href="{{ route('digital-products.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 font-semibold">
                    Browse More Resources →
                </a>
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-12 text-center">
                <svg class="w-24 h-24 text-gray-300 dark:text-slate-600 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.5a2 2 0 00-1 .25m0 0H5m0 0a2.012 2.012 0 100 4 2.012 2.012 0 000-4z"></path>
                </svg>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">No Downloads Yet</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-8">Start downloading resources to get started</p>
                <a href="{{ route('digital-products.index') }}" class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    Browse Our Collection
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
