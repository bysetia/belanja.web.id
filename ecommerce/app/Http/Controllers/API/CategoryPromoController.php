<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\CategoryPromo;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CategoryPromoController extends Controller
{
    public function index()
    {
        $category_promo = CategoryPromo::all();
        return ResponseFormatter::success($category_promo, 'Category promo list retrieved successfully');
    }

   
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'poster' => 'required|image',
            'banner' => 'required|image',
            'category_id' => 'required|exists:product_categories,id',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        $category_promo = new CategoryPromo();
        $category_promo->category_id = $request->input('category_id');

        if ($request->hasFile('poster')) {
            $file = $request->file('poster');
            $path = $file->store('public/posters');
            $path = str_replace('public/', '', $path);
            $poster_url = config('app.url') . 'ecommerce/storage/app/public/' . $path;
            $category_promo->poster = $poster_url;
        }

        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $path = $file->store('public/banners');
            $path = str_replace('public/', '', $path);
            $banner_url = config('app.url') . 'ecommerce/storage/app/public/' . $path;
            $category_promo->banner = $banner_url;
        }

        if ($category_promo->save()) {
            return ResponseFormatter::success([
                'id' => $category_promo->id,
                'poster' => $category_promo->poster,
                'banner' => $category_promo->banner,
                'category_id' => $category_promo->category_id,
                'created_at' => $category_promo->created_at,
                'updated_at' => $category_promo->updated_at,
            ], 'Category promo added successfully');
        } else {
            return ResponseFormatter::error(null, 'Category promo failed to add', 500);
        }
    }



    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'poster' => 'image',
            'banner' => 'image',
            'category_id' => 'exists:product_categories,id',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        $category_promo = CategoryPromo::findOrFail($id);

        if ($request->hasFile('poster')) {
            $file = $request->file('poster');
            $path = $file->store('public/posters');
            $path = str_replace('public/', '', $path);
            $poster_url = config('app.url') . 'ecommerce/storage/app/public/' . $path;
            $category_promo->poster = $poster_url;
        }

        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $path = $file->store('public/banners');
            $path = str_replace('public/', '', $path);
            $banner_url = config('app.url') . 'ecommerce/storage/app/public/' . $path;
            $category_promo->banner = $banner_url;
        }

        if ($request->has('category_id')) {
            $category_promo->category_id = $request->input('category_id');
        }

        if ($category_promo->save()) {
            return ResponseFormatter::success($category_promo, 'Category promo updated successfully');
        } else {
            return ResponseFormatter::error(null, 'Category promo failed to update', 500);
        }
    }

  
    public function destroy($id)
    {
        $category_promo = CategoryPromo::findOrFail($id);
        if ($category_promo->delete()) {
            return ResponseFormatter::success(null, 'Category promo deleted successfully');
        } else {
            return ResponseFormatter::error(null, 'Category promo failed to delete', 500);
        }
    }
}
