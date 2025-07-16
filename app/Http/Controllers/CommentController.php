<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
            'comment' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'post_id' => $request->post_id,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment created successfully',
            'data' => $comment
        ], 201);
    }

    public function destroy(Request $request, Comment $comment)
    {
        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }
}
