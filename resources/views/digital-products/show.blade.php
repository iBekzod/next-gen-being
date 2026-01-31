@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Product Image & Main Info -->
            <div class="lg:col-span-2">
                <!-- Image -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    @if($product->thumbnail)
                        <div class="h-96 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                            <img src="{{ Storage::url($product->thumbnail) }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="h-96 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-24 h-24 text-white mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.5a2 2 0 00-1 .25m0 0H5m0 0a2.012 2.012 0 100 4 2.012 2.012 0 000-4z"></path>
                                </svg>
                                <p class="text-white font-semibold text-lg">{{ ucfirst($product->type) }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Product Details -->
                <div class="bg-white rounded-lg shadow-md p-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $product->title }}</h1>

                    <!-- Badges -->
                    <div class="flex flex-wrap gap-3 mb-6">
                        <span class="inline-block px-4 py-2 bg-blue-100 text-blue-800 font-semibold rounded-full">
                            {{ ucfirst($product->type) }}
                        </span>
                        @if($product->is_free)
                            <span class="inline-block px-4 py-2 bg-green-100 text-green-800 font-semibold rounded-full">
                                FREE
                            </span>
                        @endif
                        @if($isPurchased)
                            <span class="inline-block px-4 py-2 bg-purple-100 text-purple-800 font-semibold rounded-full">
                                ✓ You Own This
                            </span>
                        @endif
                    </div>

                    <!-- Description -->
                    <div class="prose prose-sm max-w-none mb-8">
                        {!! nl2br(e($product->description)) !!}
                    </div>

                    <!-- Features -->
                    @if($product->features && count($product->features))
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Features</h3>
                            <ul class="space-y-3">
                                @foreach($product->features as $feature)
                                    <li class="flex items-start gap-3">
                                        <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-gray-700">{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Includes -->
                    @if($product->includes && count($product->includes))
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">What's Included</h3>
                            <div class="grid grid-cols-2 gap-4">
                                @foreach($product->includes as $item)
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M5 9V7a2 2 0 012-2h6a2 2 0 012 2v2m0 0a2 2 0 012 2v6a2 2 0 01-2 2H7a2 2 0 01-2-2v-6a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v4"></path>
                                        </svg>
                                        <span class="text-gray-700">{{ $item }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Stats -->
                    <div class="border-t border-gray-200 pt-6 flex gap-6">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Downloads</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $product->downloads_count }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Purchases</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $product->purchases_count }}</p>
                        </div>
                        @if($product->rating > 0)
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Rating</p>
                                <p class="text-2xl font-bold text-yellow-500">{{ $product->rating }}/5</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar - Purchase Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md sticky top-20 p-8">
                    <!-- Price -->
                    <div class="mb-8">
                        @if($product->is_free)
                            <p class="text-4xl font-bold text-green-600">FREE</p>
                            <p class="text-sm text-gray-600 mt-2">No payment required</p>
                        @else
                            <div class="flex items-baseline gap-2">
                                <p class="text-4xl font-bold text-gray-900">{{ $product->formatted_price }}</p>
                                @if($product->original_price && $product->original_price > $product->price)
                                    <p class="text-lg text-gray-500 line-through">${{ number_format($product->original_price, 2) }}</p>
                                    <p class="text-sm font-semibold text-red-600">Save {{ round((1 - $product->price / $product->original_price) * 100) }}%</p>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 mt-2">One-time purchase</p>
                        @endif
                    </div>

                    <!-- CTA Button -->
                    @if($isPurchased)
                        <a href="{{ route('digital-products.download-index') }}" class="w-full py-3 px-4 bg-purple-600 text-white font-bold rounded-lg hover:bg-purple-700 transition text-center block mb-4">
                            Download Your Copy
                        </a>
                    @elseif($product->is_free)
                        <form action="{{ route('digital-products.purchase', $product) }}" method="POST" class="mb-4">
                            @csrf
                            <button type="submit" class="w-full py-3 px-4 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition">
                                Get Free Access
                            </button>
                        </form>
                    @else
                        <form action="{{ route('digital-products.purchase', $product) }}" method="POST" class="mb-4">
                            @csrf
                            <button type="submit" class="w-full py-3 px-4 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition">
                                Purchase Now
                            </button>
                        </form>
                    @endif

                    <!-- Info -->
                    <div class="bg-blue-50 rounded-lg p-4 mb-6">
                        <p class="text-sm text-blue-900">
                            <span class="font-semibold">10 downloads maximum</span> per license after purchase
                        </p>
                    </div>

                    <!-- Additional Info -->
                    <div class="space-y-4 border-t border-gray-200 pt-6">
                        @if($product->tier_required !== 'free')
                            <div class="text-sm">
                                <p class="text-gray-600">Required tier:</p>
                                <p class="font-semibold text-gray-900">{{ ucfirst($product->tier_required) }} Subscription</p>
                            </div>
                        @endif

                        <div class="text-sm">
                            <p class="text-gray-600">Type:</p>
                            <p class="font-semibold text-gray-900">{{ ucfirst($product->type) }}</p>
                        </div>

                        @if($product->category)
                            <div class="text-sm">
                                <p class="text-gray-600">Category:</p>
                                <p class="font-semibold text-gray-900">{{ ucfirst($product->category) }}</p>
                            </div>
                        @endif

                        <div class="text-sm">
                            <p class="text-gray-600">Published:</p>
                            <p class="font-semibold text-gray-900">{{ $product->published_at->format('M d, Y') }}</p>
                        </div>
                    </div>

                    <!-- Login Prompt -->
                    @guest
                        <div class="mt-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                            <p class="text-sm text-yellow-900 mb-3">Sign in to purchase or download</p>
                            <a href="{{ route('login') }}" class="block text-center py-2 px-3 bg-yellow-600 text-white font-semibold rounded hover:bg-yellow-700 transition">
                                Sign In
                            </a>
                        </div>
                    @endguest
                </div>

                <!-- Related Products -->
                @if($relatedProducts->count())
                    <div class="mt-8">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Related Resources</h3>
                        <div class="space-y-4">
                            @foreach($relatedProducts as $related)
                                <a href="{{ route('digital-products.show', $related) }}" class="block bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition">
                                    <p class="font-semibold text-gray-900 mb-1 line-clamp-2">{{ $related->title }}</p>
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $related->short_description }}</p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-semibold text-blue-600">
                                            @if($related->is_free)
                                                FREE
                                            @else
                                                {{ $related->formatted_price }}
                                            @endif
                                        </span>
                                        <span class="text-xs text-gray-500">{{ ucfirst($related->type) }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
