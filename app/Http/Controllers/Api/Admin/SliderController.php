<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Slider;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\SliderResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{
    /**
     * Display a listing of the resource
     */
    public function index()
    {
        $sliders = Slider::latest()->paginate(5);
        // return new Api Resource
        return new SliderResource(true, 'List Data Sliders', $sliders);
    }

    /**
     * Store a newly created resource in storage
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'image' => 'required|image|mimes:jpeg,jpg,png|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // upload image
        $image = $request->file('image');
        $image->storeAs('public/sliders', $image->hashName());

        $slider = Slider::create([
           'image' => $image->hashName(),
        ]);

        if ($slider) {
            return new SliderResource(true, 'Data Slider Berhasil Disimpan!', $slider);
        }

        return new SliderResource(false, 'Data Slider Gagal Disimpan!', null);
    }

    /**
     * Remove the specified resource from storage
     */

    public function destroy(Slider $slider)
    {
        Storage::disk('local')->delete('public/sliders/'.basename($slider->image));

        if($slider->delete()) {
            return new SliderResource(true, 'Data Slider Berhasil Dihapus!', null);
        }

        return new SliderResource(false, 'Data Slider Gagal Dihapus!', null);
    }
}
