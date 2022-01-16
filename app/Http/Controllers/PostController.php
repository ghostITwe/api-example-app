<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function getPosts(Request $request)
    {

    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required|string",
            "anons" => "required|string",
            "text" => "required|string",
            "tags" => "sometimes",
            "image" => "required|image|max:2048|mimes:jpg,png"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        $post = new Post();
        $post->title = $request['title'];
        $post->anons = $request['anons'];
        $post->text = $request['text'];
        $post->image = 'images/' . Storage::disk('public')->put('', $request->file('image'));

        if ($request->exists('tags')) {
            $tags = explode($request['tags']);

            foreach ($tags as $tag) {
                $tagId = Tag::where('name', $tag)->get();
                $post->tags()->attach($tagId->id);
            }
        }
    }

    public function update(Request $request)
    {

    }
}
