@extends('layouts.app')

@section('title', 'Collections - Next-Gen Being')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white rounded-lg p-8">
        <h1 class="text-4xl font-bold mb-2">📚 Collections</h1>
        <p class="text-indigo-100">Curated collections of amazing content</p>
    </div>

    <!-- Collection Browser -->
    @livewire('collection-browser')
</div>
@endsection
