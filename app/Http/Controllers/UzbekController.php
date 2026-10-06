<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

/**
 * /uz - listing of every Uzbek-language post (posts.base_language = 'uz').
 * The site is English-first; without this page Uzbek posts were only
 * reachable through the mixed English feed.
 */
class UzbekController extends Controller
{
    public function index(): View
    {
        $posts = Post::published()
            ->where('base_language', 'uz')
            ->with(['author', 'category'])
            ->latest('published_at')
            ->paginate(12);

        return view('uz.index', compact('posts'));
    }
}
