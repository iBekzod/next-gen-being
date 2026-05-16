<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\TutorialCollection;
use Illuminate\View\View;

class TutorialCollectionController extends Controller
{
    public function index(): View
    {
        $collections = TutorialCollection::where('status', 'published')
            ->orderByDesc('published_at')
            ->get();

        return view('tutorial-collections.index', compact('collections'));
    }

    public function show(string $slug): View
    {
        $collection = TutorialCollection::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $posts = Post::whereIn('id', $collection->collected_content_ids ?? [])
            ->where('status', 'published')
            ->with(['category', 'author'])
            ->orderBy('series_slug')
            ->orderBy('series_part')
            ->get();

        return view('tutorial-collections.show', compact('collection', 'posts'));
    }
}
