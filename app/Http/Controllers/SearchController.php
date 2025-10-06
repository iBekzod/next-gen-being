<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Display search results.
     */
    public function index(Request $request): View
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'posts');

        // Validate search type
        if (!in_array($type, ['posts', 'authors'])) {
            $type = 'posts';
        }

        return view('search.index', [
            'query' => $query,
            'searchType' => $type,
        ]);
    }
}
