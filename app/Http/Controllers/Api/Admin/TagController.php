<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Tag;
use Illuminate\Support\Str;
use App\Http\Resources\TagResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display Listing of the resources
     */

    public function index()
    {
        // get Tags
        $tags = Tag::when(request()->q, function($tags) {
            $tags = $tags->where('name', 'like', '%'. request()->q . '%');
        })->latest()->paginate(5);

        // return API resource
        return new TagResource(true, 'List Data Tags', $tags);
    }

    /**
     * Store a newly created resource in storage
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'name' => 'required|unique:tags',
        ]);

        // cek validasi dan return error response code 422
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // tambahkan tag
        $tag = Tag::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name, '-')
        ]);

        // return success response when tag created
        if($tag) {
            return new TagResource(true, 'Data Tag Berhasil Disimpan!', $tag);
        }

        return new TagResource(false, 'Data Tag Gagal Disimpan!', null);
    }

    /**
     * Display a specific tag
     *
     */

    public function show($id)
    {
        $tag = Tag::whereId($id)->first();
        if($tag) {
            // return success with API Resource
            return new TagResource(true, 'Detail Data Tag!', $tag);
        }

        // return failed with API Resource
        return new TagResource(false, 'Detail Data Tag Tidak Ditemukan!', null);
    }

    /**
     * Update Specified Data
     */
    public function update(Request $request, Tag $tag)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:tags,name,'.$tag->id,
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // update tag
        $tag->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name, '-'),
        ]);

        if($tag) {
            return new TagResource(true, 'Data Tag Berhasil di Update!', $tag);
        }

        return new TagResource(false, 'Data Tag Gagal di Update', null);
    }

    public function destroy(Tag $tag)
    {
        if($tag->delete()) {
            // return success dengan API Resource
            return new TagResource(true, 'Data Tag Berhasil Dihapus!', null);
        }

        // return failed dengan API Resource
        return new TagResource(false, 'Data Tag Gagal Dihapus!', null);
    }
}
