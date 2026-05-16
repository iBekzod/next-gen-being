<?php

namespace App\Http\Controllers;

use App\Models\LearningPath;
use Illuminate\View\View;

class LearningPathController extends Controller
{
    public function index(): View
    {
        $paths = LearningPath::where('status', 'active')
            ->with(['user', 'items.post.category'])
            ->withCount('items')
            ->orderByDesc('created_at')
            ->get();

        return view('learning-paths.index', compact('paths'));
    }

    public function show(LearningPath $learningPath): View
    {
        if ($learningPath->status !== 'active') abort(404);
        $learningPath->load(['user', 'items' => fn($q) => $q->orderBy('order'), 'items.post.category', 'items.post.author']);

        return view('learning-paths.show', ['path' => $learningPath]);
    }
}
