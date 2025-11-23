<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Post;
use App\Services\CollectionService;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function __construct(private CollectionService $collectionService) {}

    public function index()
    {
        return response()->json(
            $this->collectionService->getCreatorCollections(auth()->user())
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'cover_image_url' => 'nullable|url',
            'is_public' => 'boolean',
        ]);

        $result = $this->collectionService->createCollection(auth()->user(), $validated);

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    public function show(Collection $collection)
    {
        $this->collectionService->recordView($collection);
        $details = $this->collectionService->getCollectionDetails($collection);

        return response()->json($details);
    }

    public function update(Request $request, Collection $collection)
    {
        if ($collection->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
            'cover_image_url' => 'nullable|url',
            'is_public' => 'boolean',
        ]);

        $result = $this->collectionService->updateCollection($collection, $validated);

        return response()->json($result);
    }

    public function destroy(Collection $collection)
    {
        if ($collection->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->collectionService->deleteCollection($collection);

        return response()->json($result);
    }

    public function addPost(Request $request, Collection $collection)
    {
        if ($collection->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate(['post_id' => 'required|exists:posts,id']);
        $post = Post::find($validated['post_id']);

        $result = $this->collectionService->addPostToCollection($collection, $post);

        return response()->json($result);
    }

    public function removePost(Collection $collection, Post $post)
    {
        if ($collection->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->collectionService->removePostFromCollection($collection, $post);

        return response()->json($result);
    }

    public function toggleSave(Collection $collection)
    {
        $result = $this->collectionService->toggleSaveCollection($collection, auth()->user());

        return response()->json($result);
    }

    public function publicCollections(Request $request)
    {
        $limit = $request->get('limit', 20);
        $days = $request->get('days', 30);

        $collections = $this->collectionService->getPublicCollections($limit, $days);

        return response()->json($collections);
    }

    public function trending(Request $request)
    {
        $limit = $request->get('limit', 10);
        $days = $request->get('days', 7);

        $trending = $this->collectionService->getTrendingCollections($limit, $days);

        return response()->json($trending);
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 20);

        $results = $this->collectionService->searchCollections($query, $limit);

        return response()->json($results);
    }

    public function mySaved()
    {
        $collections = $this->collectionService->getUserSavedCollections(auth()->user());

        return response()->json($collections);
    }
}
