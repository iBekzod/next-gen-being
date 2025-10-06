<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    public function rss(): Response
    {
        $posts = Post::published()
            ->with(['author', 'category'])
            ->latest('published_at')
            ->take(50)
            ->get();

        return response()
            ->view('feeds.rss', compact('posts'))
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
