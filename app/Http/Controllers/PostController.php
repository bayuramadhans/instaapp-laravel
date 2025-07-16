<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Like;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with(['user', 'likes', 'comments'])
            ->withCount('likes', 'comments')
            ->latest()
            ->paginate(10);

        $posts->getCollection()->transform(function ($post) use ($request) {
            $post->is_liked = $post->isLikedBy($request->user()->id);
            return $post;
        });

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'caption' => 'nullable|string|max:2000',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePath = $request->file('image')->store('posts', 'public');

        $post = Post::create([
            'user_id' => $request->user()->id,
            'caption' => $request->caption,
            'image_url' => Storage::url($imagePath),
        ]);

        $post->load('user');
        $post->is_liked = false;
        $post->likes_count = 0;

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'data' => $post
        ], 201);
    }

    public function show(Request $request, Post $post)
    {
        $post->load(['user', 'likes']);
        $post->is_liked = $post->isLikedBy($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    }

    public function update(Request $request, Post $post)
    {
        if ($post->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'caption' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = [];

        if ($request->has('caption')) {
            $updateData['caption'] = $request->caption;
        }

        if ($request->hasFile('image')) {
            // Delete old image
            if ($post->image_url) {
                $oldImagePath = str_replace('/storage/', '', $post->image_url);
                Storage::disk('public')->delete($oldImagePath);
            }

            $imagePath = $request->file('image')->store('posts', 'public');
            $updateData['image_url'] = Storage::url($imagePath);
        }

        $post->update($updateData);
        $post->load('user');
        $post->is_liked = $post->isLikedBy($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => $post
        ]);
    }

    public function destroy(Request $request, Post $post)
    {
        if ($post->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Delete image file
        if ($post->image_url) {
            $imagePath = str_replace('/storage/', '', $post->image_url);
            Storage::disk('public')->delete($imagePath);
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully'
        ]);
    }

    public function like(Request $request, Post $post)
    {
        $like = Like::where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->first();

        if ($like) {
            $like->delete();
            $post->decrement('likes_count');
            $message = 'Post unliked';
            $isLiked = false;
        } else {
            Like::create([
                'user_id' => $request->user()->id,
                'post_id' => $post->id,
            ]);
            $post->increment('likes_count');
            $message = 'Post liked';
            $isLiked = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_liked' => $isLiked,
            'likes_count' => $post->fresh()->likes_count
        ]);
    }

    public function userPosts(Request $request, $userId)
    {
        $posts = Post::where('user_id', $userId)
            ->with(['user', 'likes'])
            ->withCount('likes')
            ->latest()
            ->paginate(10);

        $posts->getCollection()->transform(function ($post) use ($request) {
            $post->is_liked = $post->isLikedBy($request->user()->id);
            return $post;
        });

        return response()->json([
            'success' => true,
            'posts' => $posts
        ]);
    }
}
