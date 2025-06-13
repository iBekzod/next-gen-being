<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    // Show paginated blog posts
    public function index()
    {
        $blogs = Blog::where('status', 'published')
            ->latest()
            ->paginate(6);

        return view('client.home', compact('blogs'));
    }

    // Show a single blog post
    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)->where('status', 'published')->firstOrFail();

        return view('client.blog', compact('blog'));
    }
}

