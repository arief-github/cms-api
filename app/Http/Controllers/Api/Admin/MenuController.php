<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Resources\MenuResource;
use App\Models\Menu;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    /**
     *  Display Listing of Resources
     */
    public function index()
    {
        $menus = Menu::when(request()->q, function($menus) {
           $menus = $menus->where('name', 'like', '%'.request()->q.'%');
        })->latest()->paginate(10);

        // return with Api Resource
        return new MenuResource(true, 'List Data Menu', $menus);
    }

    /**
     * Store newly created resource in storage
     *
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'name' => 'required',
           'url' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // creating menu
        $menu = Menu::create([
           'name' => $request->name,
           'url' => $request->url,
        ]);

        if ($menu) {
            return new MenuResource(true, 'Data Menu Berhasil Disimpan', $menu);
        }

        return new MenuResource(false, 'Data Menu Gagal Disimpan', null);
    }

    /**
     * Display the specified resource
     */
    public function show($id)
    {
        $menu = Menu::whereId($id)->first();

        if($menu) {
            return new MenuResource(true, 'Detail Data Menu!', $menu);
        }

        return new MenuResource(false, 'Detail Data Menu Tidak Ditemukan', null);
    }

    /**
     * Update the specified resource
     */

    public function update(Request $request, Menu $menu)
    {
        $validator = Validator::make($request->all(), [
           'name' => 'required',
           'url' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $menu->update([
           'name' => $request->name,
           'url' => $request->url
        ]);

        if($menu) {
            return new MenuResource(true, 'Data Menu Berhasil diubah!', $menu);
        }

        return new MenuResource(false, 'Data Menu Gagal diubah!', null);
    }

    /**
     * Delete the specified resource
     */

    public function destroy(Menu $menu)
    {
        if($menu->delete()) {
            return new MenuResource(true, 'Data Menu Berhasil Dihapus', null);
        }

        return new MenuResource(false, 'Data Menu Gagal Dihapus', null);
    }
}
