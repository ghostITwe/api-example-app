<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    //FIXME: спросить у Паши, как лушче реализовать
    public function createComment(Request $request, $id)
    {
        $post = Post::query()->with('comments')->find($id);

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:255'
        ]);

        if (!$post) {
            return response()->json([
                'message' => 'Post not found'
            ], 404);
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 400);
        }

        dd(Auth::user());
        $comment = new Comment();
        $comment->text = $request['comment'];

        if (isset($request['author'])) {
            $comment->name_guest = $request['author'];
        } else {
            $comment->author_id = $request->user();
        }
        $post->comments()->save($comment);

        return response()->json([
            'status' => true,
            'comment' => $post
        ], 201);
    }

    public function deleteComment()
    {

    }
}
