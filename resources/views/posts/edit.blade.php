@extends('layouts.app')

@section('title', 'Edit · ' . $post->title)
@section('description', 'Edit blog post')

@section('content')
@include('posts._editor', [
    'formAction' => route('posts.update', $post),
    'isEdit' => true,
    'post' => $post,
    'categories' => $categories,
    'tags' => $tags,
    'allPosts' => $allPosts,
    'premiumTiers' => ['free'=>'Free','basic'=>'Basic','pro'=>'Pro','team'=>'Team'],
])
@endsection
