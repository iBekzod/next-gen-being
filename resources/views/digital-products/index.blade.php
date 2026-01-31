@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-12">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="mb-12 text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">AI Resources & Templates</h1>
            <p class="text-xl text-gray-600">Curated prompts, templates, and tutorials to accelerate your AI workflow</p>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Product Type</label>
                        <select name="type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Types</option>
                            <option value="prompt" @selected(request('type') === 'prompt')>Prompt Templates</option>
                            <option value="template" @selected(request('type') === 'template')>Templates</option>
                            <option value="tutorial" @selected(request('type') === 'tutorial')>Tutorials</option>
                            <option value="course" @selected(request('type') === 'course')>Courses</option>
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" @selected(request('category') === $category)>{{ ucfirst($category) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                        <select name="sort" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="newest" @selected(request('sort') === 'newest')>Newest</option>
                            <option value="popular" @selected(request('sort') === 'popular')>Most Popular</option>
                            <option value="price-low" @selected(request('sort') === 'price-low')>Price: Low to High</option>
                            <option value="price-high" @selected(request('sort') === 'price-high')>Price: High to Low</option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Products Grid -->
        @if($products->count())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                @foreach($products as $product)
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden">
                        <!-- Product Image -->
                        @if($product->thumbnail)
                            <div class="h-48 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center overflow-hidden">
                                <img src="{{ Storage::url($product->thumbnail) }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="h-48 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                <div class="text-center">
                                    <svg class="w-16 h-16 text-white mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.5a2 2 0 00-1 .25m0 0H5m0 0a2.012 2.012 0 100 4 2.012 2.012 0 000-4z"></path>
                                    </svg>
                                    <p class="text-white font-semibold">{{ ucfirst($product->type) }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Content -->
                        <div class="p-5">
                            <!-- Badge -->
                            <div class="flex items-center gap-2 mb-3">
                                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                                    {{ ucfirst($product->type) }}
                                </span>
                                @if($product->is_free)
                                    <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                                        FREE
                                    </span>
                                @endif
                            </div>

                            <!-- Title -->
                            <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">{{ $product->title }}</h3>

                            <!-- Description -->
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $product->short_description ?? substr($product->description, 0, 100) }}</p>

                            <!-- Stats -->
                            <div class="flex items-center gap-4 mb-4 text-xs text-gray-500">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z"></path>
                                    </svg>
                                    {{ $product->downloads_count }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 10.5a1.5 1.5 0 113 0v-6a1.5 1.5 0 01-3 0v6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.256 8H6z"></path>
                                    </svg>
                                    {{ $product->purchases_count }}
                                </div>
                            </div>

                            <!-- Price & Button -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div>
                                    @if($product->is_free)
                                        <p class="text-lg font-bold text-green-600">FREE</p>
                                    @else
                                        <p class="text-lg font-bold text-gray-900">{{ $product->formatted_price }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('digital-products.show', $product) }}" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center mb-12">
                {{ $products->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No products found</h3>
                <p class="text-gray-600">Try adjusting your filters</p>
            </div>
        @endif
    </div>
</div>
@endsection
