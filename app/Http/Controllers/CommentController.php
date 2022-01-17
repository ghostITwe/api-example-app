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
            'author' => Rule::requiredIf(!Auth::check()),
            'comment' => 'required|string|max:255'
        ]);

        if (!$post) {
            return response()->json([
                'message' => 'Post not found'
            ])->setStatusCode(404, 'Post not found');
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ])->setStatusCode(400, 'Creating error');
        }

        $comment = new Comment();
        $comment->text = $request['comment'];

        if (isset($request['author']) && !Auth::check()) {
            $comment->name_guest = $request['author'];
        } else {
            $comment->author_id = Auth::id();
        }
        $post->comments()->save($comment);

        return response()->json([
            'status' => true,
        ])->setStatusCode(201, 'Successful creation');
    }

    public function deleteComment($postId, $commentId)
    {
        $comment = Comment::query()->find($commentId);
        $post = Post::query()->with('comments')->find($postId);

        if (!$post) {
            return response()->json([
                'message' => 'Post not found'
            ])->setStatusCode(404, 'Post not found');
        }

        if (!$comment) {
            return response()->json([
                'message' => 'Comment not found'
            ])->setStatusCode(404, 'Comment not found');
        }

        $post->comments()->where('comments.id', $comment->id)->delete();

        return response()->json([
            'status' => true
        ])->setStatusCode(201, 'Successful delete');
    }
}
