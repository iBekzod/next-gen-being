<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a category and its posts.
     */
    public function show(Category $category): View
    {
        // Eager load relationships
        $category->load('publishedPosts.author', 'publishedPosts.category', 'publishedPosts.tags');

        return view('categories.show', compact('category'));
    }
}
