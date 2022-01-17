<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function searchPost($name)
    {
        $postsData = Post::query()
            ->with('tags')
            ->whereHas('tags', function($q) use ($name){
                $q->where('name', $name);
            })
            ->get();

        $posts = [];

        foreach ($postsData as $key => $post) {
            $tags = [];
            foreach ($post->tags as $tag) {
                $tags [] = $tag->name;
            }
            $date = new DateTime($post->created_at);
            $posts[$key]['title'] = $post->title;
            $posts[$key]['datatime'] = $date->format('H:i d.m.Y');;
            $posts[$key]['anons'] = $post->anons;
            $posts[$key]['text'] = $post->text;
            $posts[$key]['tags'] = $tags;
            $posts[$key]['image'] = $post->image;
        }

        return response()->json([
            $posts
        ])->setStatusCode(200, 'Found posts');
    }

    //FIXME: Подумать как переделать массив. ДОГАДКА В 2:17 ночи select протестить
    public function getPosts()
    {
        $posts = Post::query()->with('tags')->get();

        return response()->json([
            'post_id' => $posts
        ])->setStatusCode(200, 'List posts');
    }

    public function getPost($id)
    {
        $post = Post::query()->with([
            'tags',
            'comments'
        ])->find($id);

        if (!$post) {
            return response()->json([
                'message' => 'Post not found'
            ])->setStatusCode(404, 'Post not found');
        }

        $tags = [];
        $comments = [];
        $date = new DateTime($post->created_at);

        foreach ($post->tags as $tag) {
            $tags [] = $tag->name;
        }

        foreach ($post->comments as $key => $comment) {
            $commentDate = new DateTime($comment->created_at);
            $user = User::query()->find($comment->author_id);

            $comments [$key]['comment_id'] = $comment->id;
            $comments [$key]['datatime'] = $commentDate->format('H:i d.m.Y');
            $comments [$key]['author'] =  !empty($user) && $user->isAdmin() ? 'admin' : $comment->name_guest;
            $comments [$key]['comment'] = $comment->text;
        }

        return response()->json([
            'title' => $post->title,
            'datatime' => $date->format('H:i d.m.Y'),
            'anons' => $post->anons,
            'text' => $post->text,
            'tags' => $tags,
            'image' => $post->image,
            'comments' => $comments
        ])->setStatusCode(200, 'View Post');
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required|string|unique:posts",
            "anons" => "required|string",
            "text" => "required|string",
            "tags" => "sometimes",
            "image" => "required|image|max:2048|mimes:jpg,png"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ],400);
        }

        $post = new Post();
        $post->title = $request['title'];
        $post->anons = $request['anons'];
        $post->text = $request['text'];
        $post->image = 'images/' . Storage::disk('public')->put('', $request->file('image'));
        $post->save();

        if (isset($request['tags'])) {
            $tags = explode(',',$request['tags']);

            foreach ($tags as $tag) {
                $tagId = Tag::query()->where('name', $tag)->get();

                if ($tagId->isEmpty()) {
                    $post->tags()->attach($this->createTag($tag));
                } else {
                    $post->tags()->attach($tagId);
                }
            }
        }

        return response()->json([
            'status' => true,
            'post_id' => $post->id
        ], 201);
    }

    /**
     * @throws \Exception
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required|string|unique:posts",
            "anons" => "sometimes|string",
            "text" => "sometimes|string",
            "tags" => "sometimes",
            "image" => "sometimes|image|max:2048|mimes:jpg,png"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ])->setStatusCode(400, 'Editing error');
        }

        $post = Post::query()->find($id);

        if (!$post) {
            return response()->json([
                'message' => 'Post not found'
            ],404);
        }

        $post->title = $request['title'];
        $post->anons = $request['anons'];
        $post->text = $request['text'];
        $post->image = 'images/' . Storage::disk('public')->put('', $request->file('image'));
        $post->save();

        if (isset($request['tags'])) {
            $tags = explode(',',$request['tags']);

            foreach ($tags as $tag) {
                $tagId = Tag::query()->where('name', $tag)->get();

                if ($tagId->isEmpty()) {
                    $post->tags()->attach($this->createTag($tag));
                } else {
                    $post->tags()->attach($tagId);
                }
            }
        }

        $date = new DateTime($post->created_at);

        return response()->json([
            'status' => true,
            'post' => [
                'title' => $post->title,
                'datatime' => $date->format("H:i d.m.Y"),
                'anons' => $post->anons,
                'text' => $post->text,
                'tags' => [
                    $post->tags
                ],
                'image' => $post->image
            ]
        ],201);
    }

    public function delete($id) {
        $post = Post::query()->find($id);

        if (!$post) {
            return response()->json([
                'message' => 'Post not found'
            ])->setStatusCode(404, 'Page not found');
        }

        $post->delete();

        return response()->json([
            'status' => true
        ])->setStatusCode(201, 'Successful delete');
    }

    private function createTag($name) {
        $tag = new Tag();
        $tag->name = $name;
        $tag->save();

        return $tag;
    }
}
