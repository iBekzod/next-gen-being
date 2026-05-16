@extends('layouts.app')

@section('title', 'New post · ' . setting('site_name'))
@section('description', 'Create a new blog post')

@section('content')
@include('posts._editor', [
    'formAction' => route('posts.store'),
    'isEdit' => false,
    'post' => null,
    'categories' => $categories,
    'tags' => $tags,
    'allPosts' => $allPosts,
    'premiumTiers' => $premiumTiers,
])
@endsection
