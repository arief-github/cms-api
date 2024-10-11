<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Post;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource
     *
     */
    public function index()
    {
        $posts = Post::with('users', 'category', 'comments')->when(request()->q, function($posts) {
           $posts = $posts->where('title', 'like', '%', request()->q. '%');
        })->latest()->paginate(5);

        return new PostResource(true, 'List Data Posts', $posts);
    }

    /**
     * Store a newly created resource in storage
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'image' => 'required|image|mimes:jpeg,jpg,png|max:2000',
            'title' => 'required|unique:posts',
            'category_id' => 'required',
            'content' => 'required|string',
            'description' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        $post = Post::create([
           'image' => $image->hashName(),
            'title' => $request->title,
            'slug' => Str::slug($request->title, '-'),
            'category_id' => $request->category_id,
            'user_id' => auth()->guard('api')->user()->id,
            'content' => $request->content,
            'description' => $request->description,
        ]);

        // assign tags
        $post->tags()->attach($request->tags);
        $post->save();

        if($post) {
            return new PostResource(true, 'Data Post Berhasil Disimpan', $post);
        }

        return new PostResource(false, 'Data Post Gagal Disimpan', null);
    }

    /**
     * Display the specified resource
     *
     */
    public function show($id)
    {
        $post = Post::with('tags', 'category')->whereId($id)->first();

        if($post) {
            // return success with Api Resource
            return new PostResource(true, 'Detail Data Post!', $post);
        }

        // return failed with Api Resource
        return new PostResource(false, 'Detail Data Post Tidak Ditemukan', null);
    }

    /**
     * Update the specified resource in storage
     */

    public function update(Request $request, Post $post)
    {
        $validator = Validator::make($request->all(), [
           'title' => 'required|unique:posts,title,'.$post->id,
            'category_id' => 'required',
            'content' => 'required',
            'description' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([$validator->errors()], 422);
        }

        // check image update
        if($request->file('image')) {
            // remove old image
            Storage::disk('local')->delete('public/posts/'.basename($post->image));

            // upload new image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            $post->update([
               'image' => $image->hashName(),
                'title' => $request->title,
                'slug' => Str::slug($request->title, '-'),
                'category_id' => $request->category_id,
                'user_id' => auth()->guard('api')->user()->id,
                'content' => $request->content,
                'description' => $request->description,
            ]);
        }

        // upload without image
        $post->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title, '-'),
            'category_id' => $request->category_id,
            'user_id' => auth()->guard('api')->user()->id,
            'content' => $request->content,
            'description' => $request->description,
        ]);

        // sync tags
        $post->tags()->sync($request->tags);
        $post->save();

        if($post) {
            return new PostResource(true, 'Data Post Berhasil Diupdate!', $post);
        }

        return new PostResource(false, 'Data Post Gagal Diupdate!', null);

    }

    /**
     * Remove the specified resource from storage
     */

    public function destroy(Post $post)
    {
        $post->tags()->detach();

        // remove image
        Storage::disk('local')->delete('public/posts/'.basename($post->image));

        if($post->delete()) {
            return new PostResource(true, 'Data Post Berhasil Dihapus', null);
        }

        return new PostResource(false, 'Data Post Gagal Dihapus!', null);
    }
}
