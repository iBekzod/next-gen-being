<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\View\View;

class TagController extends Controller
{
    /**
     * Display a tag and its posts.
     */
    public function show(Tag $tag): View
    {
        // Eager load relationships
        $tag->load('publishedPosts.author', 'publishedPosts.category', 'publishedPosts.tags');

        return view('tags.show', compact('tag'));
    }
}
