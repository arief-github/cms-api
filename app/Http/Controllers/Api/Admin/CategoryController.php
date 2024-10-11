<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display Listing of the resource
     */

    public function index()
    {
        $categories = Category::when(request()->q, function($categories) {
           $categories = $categories->where('name', 'like', '%'. request()->q . '%');
        })->latest()->paginate(5);

        // return api resource
        return new CategoryResource(true, 'List Data Categories', $categories);
    }

    /**
     * Store a newly created category
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2000',
            'name' => 'required|unique:categories',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // upload image code
        $image = $request->file('image');
        $image->storeAs('public/categories', $image->hashName());

        // create data category
        $category = Category::create([
            'image' => $image->hashName(),
            'name' => $request->name,
            'slug' => Str::slug($request->name, '-'),
        ]);

        if(!$category) {
            return new CategoryResource(false, 'Data Category Gagal Disimpan!', null);
        }

        return new CategoryResource(true, 'Data Category Berhasil Disimpan!', $category);
    }

    /**
     * Displayed the specific resource
     */

    public function show($id)
    {
        $category = Category::whereId($id)->first();

        if($category) {
            return new CategoryResource(true, 'Detail Data Category!', $category);
        }

        return new CategoryResource(false, 'Detail Data Category Ditemukan!', null);
    }

    /**
     * Update the specified resource in storage
     */

    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
           'name' => 'required|unique:categories,name,'.$category->id,
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // check if there is image update
        if ($request->file('image')) {
            // remove previous/old image
            Storage::disk('local')->delete('public/categories/'.basename($category->image));

            // upload new image
            $image = $request->file('image');
            $image->storeAs('public/categories', $image->hashName());

            // update category with new image
            $category->update([
                'image' => $image->hashName(),
                'name' => $request->name,
                'slug' => Str::slug($request->name, '-')
            ]);
        }

        // update category without image
        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name, '-')
        ]);

        if ($category) {
            // return success with API Resource
            return new CategoryResource(true, 'Data Category Berhasil Diubah!', $category);
        }

        return new CategoryResource(false, 'Data Category Gagal Diubah!', null);
    }

    /**
     * Delete Category
     */

    public function destroy(Category $category)
    {
        Storage::disk('local')->delete('public/categories/'.basename($category->image));

        if($category->delete()) {
            // return success with Api Resource
            return new CategoryResource(true, 'Data Category Berhasil Dihapus!', null);
        }

        // return failed with Api Resource
        return new CategoryResource(false, 'Data Category Gagal Dihapus!', null);
    }
}
