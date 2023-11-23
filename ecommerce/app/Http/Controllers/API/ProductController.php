<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use App\Models\City;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
        private function getCityIdByRegencyName($regencyName)
    {
        $city = City::where('title', $regencyName)->first();
    
        if ($city) {
            return $city->city_id;
        } else {
            return null;
        }
    }

    public function all(Request $request)
    {
        $id = $request->input('id');
        // $limit = $request->input('limit', 12);
        $name = $request->input('name');
        $description = $request->input('description');
        $category_id = $request->input('category_id');
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');
        $rate_from = $request->input('rate_from');
        $rate_to = $request->input('rate_to');
        $user_id = $request->input('user_id');
        $store_id = $request->input('store_id');
        $rate = $request->input('rate');
        
    
        if ($id) {
            $product = Product::with(['category', 'user', 'store',])->find($id);
            // $product = Product::with(['category', 'galleries', 'user', 'store'])->find($id);
            // $product = Product::with(['category:id', 'galleries:id', 'user:id'])->find($id);
    
    
            if ($product) {
                $product->picturePath = 'https://belanja.penuhmakna.co.id/' . $product->picturePath;
    
                return ResponseFormatter::success(
                    $product,
                    'Product data retrieved successfully'
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Product data not found',
                    404
                );
            }
        }
    
        $productQuery = Product::with(['category', 'user', 'store']);
        // $productQuery = Product::with(['category', 'galleries', 'user', 'store']);
        // $productQuery = Product::with(['category:id', 'galleries:id', 'user:id']);
    
        if ($name) {
            $productQuery->where('name', 'like', '%' . $name . '%');
        }
    
        if ($description) {
            $productQuery->where('description', 'like', '%' . $description . '%');
        }
    
        if ($price_from) {
            $productQuery->where('price', '>=', $price_from);
        }
    
        if ($price_to) {
            $productQuery->where('price', '<=', $price_to);
        }
    
        if ($rate_from) {
            $productQuery->where('rate', '>=', $rate_from);
        }
    
        if ($rate_to) {
            $productQuery->where('rate', '<=', $rate_to);
        }
    
        if ($category_id) {
            $productQuery->where('category_id', $category_id);
        }
    
        if ($user_id) {
            $productQuery->where('user_id', $user_id);
        }
        
        if ($store_id) {
            $productQuery->where('store_id', $store_id); // Filter berdasarkan store_id
        }
        
         if ($rate) {
            $productQuery->where('rate', $rate); // Filter berdasarkan store_id
        }
         
        
        // $paginator = $productQuery->paginate($limit);
    
        // $products = $paginator->getCollection();
            $productQuery->latest();
        
         $products = $productQuery->get();
        
        $response = [];
        
        foreach ($products as $product) {
            $storeId = $product->store_id;
            $store = Store::find($storeId);
            
            if ($store) {
                $productData = $product->toArray(); // Convert the product object to an array
        
                $regencyName = $store->regencies;
                $cityId = $this->getCityIdByRegencyName($regencyName);
        
                $productData['store'] = $store->toArray(); // Convert the store object to an array
                $productData['store']['city_id'] = $cityId; // Set the city_id value
                
                $reviewCount = $product->reviews()->count();
                $productData['review'] = $reviewCount;
                
                $averageRating = $product->reviews()->avg('rate'); 
                $productData['average_rating'] = $averageRating; 
                $response[] = $productData; // Add the product data to the response data
            }
        }
        
        return ResponseFormatter::success(
            $response,
            'Product list data successfully retrieved'
        );

}

    public function postProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|integer',
            'category_id' => 'required|exists:product_categories,id',
            'picturePath' => 'required|image',
            'sku' => 'nullable|unique:products',
            'kondisi_produk' => 'in:baru,bekas',
            'photo1' => 'nullable|image',
            'photo2' => 'nullable|image',
            'photo3' => 'nullable|image',
            'photo4' => 'nullable|image',
            
        ]);
    
        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }
    
        $product = new Product();
        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->price = $request->input('price');
        $product->quantity = $request->input('quantity');
        $product->slug = $request->input('slug');
        $product->product_origin = $request->input('product_origin');
        $product->sku = $request->input('sku');
        $product->product_material = $request->input('product_material');
        $product->weight = $request->input('weight');
        $product->kondisi_produk = $request->input('kondisi_produk');
        $product->category_id = $request->input('category_id');
        $product->user_id = auth()->user()->id;
        $product->store_id = Auth::user()->store->id;
    
        if ($product->save()) {
            if ($request->hasFile('picturePath')) {
                $file = $request->file('picturePath');
                $path = $file->store('public/picturePath');
                $path = str_replace('public/', '', $path);
                $picturePath = config('app.url') . '/ecommerce/storage/app/public/' . $path;
                $product->picturePath = $picturePath; // Simpan URL lengkap
                $product->save();
            }
            
            if ($request->hasFile('photo1')) {
                 $file = $request->file('photo1');
                $path = $file->store('public/pictures');
                $path = str_replace('public/', '', $path);
                $picturePath = config('app.url') . '/ecommerce/storage/app/public/' . $path;
                $product->photo1 = $picturePath; // Simpan URL lengkap
                $product->save();
            }
            if ($request->hasFile('photo2')) {
                $file = $request->file('photo2');
                $path = $file->store('public/pictures');
                $path = str_replace('public/', '', $path);
                $picturePath = config('app.url') . '/ecommerce/storage/app/public/' . $path;
                $product->photo2 = $picturePath; // Simpan URL lengkap
                $product->save();
            }
            if ($request->hasFile('photo3')) {
                $file = $request->file('photo3');
                $path = $file->store('public/pictures');
                $path = str_replace('public/', '', $path);
                $picturePath = config('app.url') . '/ecommerce/storage/app/public/' . $path;
                $product->photo3 = $picturePath; // Simpan URL lengkap
                $product->save();
            }
            if ($request->hasFile('photo4')) {
                $file = $request->file('photo4');
                $path = $file->store('public/pictures');
                $path = str_replace('public/', '', $path);
                $picturePath = config('app.url') . '/ecommerce/storage/app/public/' . $path;
                $product->photo4 = $picturePath; // Simpan URL lengkap
                $product->save();
            }
    
            $product->refresh();
            return ResponseFormatter::success($product, 'Product added successfully');
        } else {
            return ResponseFormatter::error(null, 'Product failed to add', 500);
        }
    }
    
    public function editProduct(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return ResponseFormatter::error(
                null,
                'Product not found',
                404
            );
        }
    
        if (!auth()->check()) {
            return ResponseFormatter::error(null, 'Unauthorized', 401);
        }
    
        // if ($product->user_id !== auth()->user()->id) {
        //     return ResponseFormatter::error(null, 'You do not have permission to edit this product', 403);
        // }
    
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'description' => 'string',
            'price' => 'integer',
            'category_id' => 'exists:product_categories,id',
            'picturePath' => 'nullable|image',
            'sku' => 'nullable|unique:products,sku,' . $id,
            'kondisi_produk' => 'in:baru,bekas',
            'photo1' => 'nullable|image',
            'photo2' => 'nullable|image',
            'photo3' => 'nullable|image',
            'photo4' => 'nullable|image',
        ]);
    
        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }
    
        $product->fill($request->all());
    
        if ($product->save()) {
            if ($request->hasFile('picturePath')) {
               $file = $request->file('picturePath');
                $path = $file->store('public/picturePath');
                $path = str_replace('public/', '', $path);
                $picturePath = config('app.url') . '/ecommerce/storage/app/public/' . $path;
                $product->picturePath = $picturePath; // Simpan URL lengkap
                $product->save();
            }
             if ($request->hasFile('photo1')) {
                 $file = $request->file('photo1');
                $path = $file->store('public/pictures');
                $path = str_replace('public/', '', $path);
                $picturePath = config('app.url') . '/ecommerce/storage/app/public/' . $path;
                $product->photo1 = $picturePath; // Simpan URL lengkap
                $product->save();
            }
            if ($request->hasFile('photo2')) {
                $file = $request->file('photo2');
                $path = $file->store('public/pictures');
                $path = str_replace('public/', '', $path);
                $picturePath = config('app.url') . '/ecommerce/storage/app/public/' . $path;
                $product->photo2 = $picturePath; // Simpan URL lengkap
                $product->save();
            }
            if ($request->hasFile('photo3')) {
                $file = $request->file('photo3');
                $path = $file->store('public/pictures');
                $path = str_replace('public/', '', $path);
                $picturePath = config('app.url') . '/ecommerce/storage/app/public/' . $path;
                $product->photo3 = $picturePath; // Simpan URL lengkap
                $product->save();
            }
            if ($request->hasFile('photo4')) {
                $file = $request->file('photo4');
                $path = $file->store('public/pictures');
                $path = str_replace('public/', '', $path);
                $picturePath = config('app.url') . '/ecommerce/storage/app/public/' . $path;
                $product->photo4 = $picturePath; // Simpan URL lengkap
                $product->save();
            }
    
            return ResponseFormatter::success($product, 'Product updated successfully');
        } else {
            return ResponseFormatter::error(null, 'Product failed to update', 500);
        }
    }
    
    
        public function updatePhoto(Request $request, $id)
    {
        // Validasi, diperlukan file dengan tipe image
        $validator = Validator::make($request->all(), [
            'picturePath' => 'required|image'
        ]);
    
        // Jika validasi gagal
        if ($validator->fails()) {
            return ResponseFormatter::error(
                ['error' => $validator->errors()],
                'Update photo fails',
                401
            );
        }
    
        // Jika validasi berhasil -> cek apakah ada file yang diunggah
        if ($request->hasFile('picturePath')) {
            // Mengambil file gambar dari permintaan (request)
            $file = $request->file('picturePath');
    
            // Simpan foto ke direktori public/picturePath
            $directory = 'public/picturePath';
            $picturePath = $file->store($directory);
    
            // Cek apakah file berhasil diunggah
            if (!$picturePath) {
                return ResponseFormatter::error(
                    null,
                    'File upload failed',
                    500
                );
            }
    
         
            // Dapatkan URL lengkap dengan menggabungkan URL situs dan jalur file
            $fullPicturePath = config('app.url') . 'ecommerce/storage/app/' . $picturePath;
    
    
    
            // Simpan foto ke database (URL-nya)
            $product = Product::find($id);
            if (!$product) {
                return ResponseFormatter::error(
                    null,
                    'Product not found',
                    404
                );
            }
            $product->picturePath =  $fullPicturePath;
            $product->save();
    
            // Membuat respons dengan format yang diinginkan
            $response = [
                'picturePath' =>  $fullPicturePath
            ];
    
            return ResponseFormatter::success([$response], 'File successfully uploaded');
        }
    }
    
        public function deleteProduct($id)
        {
            // Cari produk berdasarkan ID
            $product = Product::find($id);
    
            // Jika produk tidak ditemukan
            if (!$product) {
                return ResponseFormatter::error(
                    null,
                    'Product not found',
                    404
                );
            }
    
            // Hapus gambar produk jika ada
            if ($product->picturePath) {
                Storage::delete($product->picturePath);
            }
    
            // Soft delete produk
            $product->delete();
    
            // Mengembalikan data produk yang dihapus
            // $product = Product::withTrashed()->find($id);
            // atau
            // $products = Product::withTrashed()->get();
    
            // Menghapus permanen data produk
            // $product->forceDelete();
    
            return ResponseFormatter::success(null, 'Product deleted successfully');
        }
    }
