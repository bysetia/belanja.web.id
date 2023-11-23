<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductGallery;
use Illuminate\Support\Facades\Validator;

class GalleryProductController extends Controller
{
    public function addGallery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products_id' => 'required|exists:products,id',
            'url' => 'required|image',
             // aturan validasi lainnya...
            'selectProduct' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        $product = Product::find($request->input('products_id'));

        if (!$product) {
            return ResponseFormatter::error(
                null,
                'Product Data not found',
                404
            );
        }

        $file = $request->file('url');
        $path = $file->store('public/gallery');
        


        $gallery = ProductGallery::create([
            'products_id' => $product->id,
            'url' => $path,
            'selectProduct' => $request->input('selectProduct'), // Menyimpan nilai selectProduct dari request
        ]);
        $response = $gallery->toArray();
       $response = [
            'id' => $response['id'],
            'url' => $response['url'],
             'selectProduct' => $response['selectProduct'],
            'created_at' => $response['created_at'],
            'updated_at' => $response['updated_at'],
        ];

        return ResponseFormatter::success($response, 'Product photo added successfully');
    }

    public function getAllGallery()
    {
        $galleries = ProductGallery::paginate(6);

        if ($galleries->isEmpty()) {
            return ResponseFormatter::error(
                null,
                'No galleries found',
                404
            );
        }

          $response = $galleries->toArray();

        // Menghapus bagian 'user_id' dari setiap item di dalam 'data'
        $responseData = $response['data'];
        $modifiedData = [];
        foreach ($responseData as $item) {
            $modifiedItem = [
            'id' => $item['id'],
            'products_id' => $item['products_id'],
            'url' => $item['url'],
            'selectProduct' => $item['selectProduct'],
            'deleted_at' => $item['deleted_at'],
            'created_at' => $item['created_at'],
            'updated_at' => $item['updated_at'],
            
        ];
        $modifiedData[] = $modifiedItem;
        }
    
        $response['data'] = $modifiedData;
    
        return ResponseFormatter::success($response, 'All Product Photos Successfully taken');
    }
    
    
    public function deleteGallery($id)
    {
        $gallery = ProductGallery::find($id);

        if (!$gallery) {
            return ResponseFormatter::error(null, 'Photos not found', 404);
        }

        $gallery->delete();

        return ResponseFormatter::success(null, 'Photo successfully deleted');
    }
}
