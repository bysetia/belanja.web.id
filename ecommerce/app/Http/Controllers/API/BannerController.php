<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Models\Banner;
use App\Helpers\ResponseFormatter;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::all();
        return ResponseFormatter::success($banners, 'Banners retrieved successfully');
    }

    public function show($id)
    {
        $banner = Banner::find($id);
        if ($banner) {
            return ResponseFormatter::success(['id' => $banner->id] + $banner->toArray(), 'Banner retrieved successfully');
        } else {
            return ResponseFormatter::error(null, 'Banner not found', 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        $baseUrl = 'http://belanja.web.test/';

        $imagePath = $request->file('image')->store('public/bannerPromo');
        $imageUrl = $baseUrl . 'ecommerce/storage/app/' . $imagePath;

        $banner = Banner::create([
            'image' => $imageUrl,
        ]);

        return ResponseFormatter::success(['id' => $banner->id] + $banner->toArray(), 'Banner created successfully', 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        $banner = Banner::find($id);
        if (!$banner) {
            return ResponseFormatter::error(null, 'Banner not found', 404);
        }

        $baseUrl = 'http://belanja.web.test/';

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/bannerPromo');
            $imageUrl = $baseUrl . 'ecommerce/storage/app/' . $imagePath;
            $banner->image = $imageUrl;
        }

        $banner->save();

        return ResponseFormatter::success(['id' => $banner->id] + $banner->toArray(), 'Banner updated successfully');
    }



    public function destroy($id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return ResponseFormatter::error(null, 'Banner not found', 404);
        }

        $banner->delete();

        return ResponseFormatter::success(null, 'Banner deleted successfully');
    }
}
