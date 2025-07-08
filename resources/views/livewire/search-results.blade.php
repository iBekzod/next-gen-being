@extends('layouts.app')

@section('title', 'Search Results' . ($query ? ' for "' . $query . '"' : ''))
@section('description', 'Search results for articles and authors on ' . setting('site_name'))

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                @if($query)
                    Search Results for "{{ $query }}"
                @else
                    Search
                @endif
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                @if($query)
                    Find articles and authors matching your search query
                @else
                    Enter a search term to find articles and authors
                @endif
            </p>
        </div>

        @livewire('search-results', ['query' => $query, 'searchType' => $searchType])
    </div>
</div>
@endsection
