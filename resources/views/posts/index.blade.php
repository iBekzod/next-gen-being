@extends('layouts.app')

@section('title', 'All Articles - ' . setting('site_name'))
@section('description', 'Browse our collection of articles, tutorials, and insights on technology and development')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    @livewire('post-list')
</div>
@endsection
