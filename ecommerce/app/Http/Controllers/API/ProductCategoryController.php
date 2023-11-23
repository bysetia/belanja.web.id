<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductCategoryController extends Controller
{
    private $siteUrl = 'https://belanja.penuhmakna.co.id/';

    public function all(Request $request)
    {
        $id = $request->input('id');
        // $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $show_product = $request->input('show_product');

        if ($id) {
            $category = ProductCategory::with(['products'])->find($id);

            if ($category)
                return ResponseFormatter::success(
                    $category,
                    'Product category successfully fetched'
                );
            else
                return ResponseFormatter::error(
                    null,
                    'Product category not found',
                    404
                );
        }

        $category = ProductCategory::query();

        if ($name) {
            $category->where('name', 'like', '%' . $name . '%');
        }

        if ($show_product) {
            $category->with('products');
        }

        // return ResponseFormatter::success(
        //         // $category->orderBy('id')->paginate($limit),
        //     'Product category successfully fetched'
        // );
        
        $categories = $category->get(); // paginator ->limit($limit)

        return ResponseFormatter::success(
            $categories,
            'Product category successfully fetched'
        );
    }

    public function postCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'photo' => 'required|image',
            'banner' => 'required|file', // Ubah menjadi tipe file
            // 'slug' => 'required|string|unique:product_categories',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        $category = new ProductCategory();
        $category->name = $request->input('name');
        $category->slug = $request->input('slug');

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
        $path = $file->store('public/photoCategory');
        $photoUrl = Storage::url($path);
        $photoUrl = str_replace('public/', 'public/', $photoUrl);
        $category->photo = $this->siteUrl . $photoUrl;
        }
        
        /// Save banner file to storage
    if ($request->hasFile('banner')) {
        $bannerFile = $request->file('banner');
        $bannerPath = 'public/bannerCategory';
        $bannerFileName = $bannerFile->getClientOriginalName();
        $bannerFile->storeAs($bannerPath, $bannerFileName);
        $bannerUrl = Storage::url($bannerPath . '/' . $bannerFileName);
        $category->banner = $this->siteUrl . $bannerUrl;
    }


        if ($category->save()) {
            $categoryData = $category->toArray();
            $categoryData = [
                'id' => $categoryData['id'],
                'name' => $categoryData['name'],
                'photo' => $categoryData['photo'],
                'banner' => $categoryData['banner'],
                'slug' => $categoryData['slug'],
                'created_at' => $categoryData['created_at'],
                'updated_at' => $categoryData['updated_at'],
            ];

            return ResponseFormatter::success($categoryData, 'Category created successfully');
        } else {
            return ResponseFormatter::error(null, 'Category creation failed', 500);
        }
    }

    public function editCategory(Request $request, $id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return ResponseFormatter::error(
                null,
                'Product category not found',
                404
            );
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'slug' => 'string',
            'photo' => 'image',
            'banner' => 'file', // Ubah menjadi tipe file
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        // Update hanya jika ada data yang dikirimkan
        if ($request->has('name')) {
            $category->name = $request->name;
        }

        if ($request->has('slug')) {
            $category->slug = $request->slug;
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
        $path = $file->store('public/photoCategory');
        $photoUrl = Storage::url($path);
        $photoUrl = str_replace('public/', 'public/', $photoUrl);
        $category->photo = $this->siteUrl  . $photoUrl;
}
        // Save banner file to storage
    if ($request->hasFile('banner')) {
        $bannerFile = $request->file('banner');
        $bannerPath = 'public/bannerCategory';
        $bannerFileName = $bannerFile->getClientOriginalName();
        $bannerFile->storeAs($bannerPath, $bannerFileName);
        $bannerUrl = Storage::url($bannerPath . '/' . $bannerFileName);
        $category->banner = $this->siteUrl . $bannerUrl;
    }



        if ($category->save()) {
            $category->touch(); // Mengupdate updated_at pada tabel product_categories
            return ResponseFormatter::success($category, 'Category updated successfully');
        } else {
            return ResponseFormatter::error(null, 'Category update failed', 500);
        }
    }

    public function deleteCategory($id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return ResponseFormatter::error(
                null,
                'Product category not found',
                404
            );
        }

        if ($category->delete()) {
            return ResponseFormatter::success(null, 'Category deleted successfully');
        } else {
            return ResponseFormatter::error(null, 'Category deletion failed', 500);
        }
    }
}
