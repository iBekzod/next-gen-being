@extends('layouts.app')

@section('title', 'SEO Analyzer - Next-Gen Being')

@section('content')
<div class="space-y-8">
    <div class="bg-gradient-to-r from-green-600 to-green-800 text-white rounded-lg p-8">
        <h1 class="text-4xl font-bold mb-2">🔍 SEO Analyzer</h1>
        <p class="text-green-100">Optimize your content for search engines</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        @livewire('seo-analyzer')
    </div>
</div>
@endsection
