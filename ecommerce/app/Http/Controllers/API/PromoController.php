<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Promo;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Notifications\PromoAddedNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;


class PromoController extends Controller
{
        public function index()
    {
        // Retrieve all promos from the database
        $promos = Promo::all();

        return ResponseFormatter::success($promos, 'List of all promos');
    }

    public function show($id)
    {
        $promo = Promo::find($id);

        if (!$promo) {
            return ResponseFormatter::error(null, 'Data promo tidak ditemukan', 404);
        }

        return ResponseFormatter::success($promo, 'Promo found successfully');
    }

         public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'product_id' => 'required|exists:products,id',
            'after_promo' => 'required|numeric',
        ]);
    
        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }
    
        $product = Product::find($request->product_id);
    
        if (!$product) {
            return ResponseFormatter::error(null, 'Product tidak ditemukan', 404);
        }
    
        // Pastikan nilai after_promo tidak melebihi harga produk
        if ($request->after_promo > $product->price) {
            return ResponseFormatter::error(null, 'Harga setelah promo tidak boleh melebihi harga produk', 422);
        }
    
        // Cek apakah sudah ada promo yang aktif untuk produk ini
        $activePromo = Promo::where('product_id', $product->id)
                            ->where('status', 'active')
                            ->first();
                            
                            // Cek apakah sudah ada promo yang tidak aktif untuk produk ini
        $inactivePromo = Promo::where('product_id', $product->id)
                              ->where('status', 'inactive')
                              ->first();
    
        // Jika ada promo aktif, maka tidak diperbolehkan menambahkan promo baru
        if ($activePromo) {
            return ResponseFormatter::error(null, 'Produk ini sudah memiliki promo aktif', 422);
        }
        
         if ($inactivePromo) {
            return ResponseFormatter::error(null, 'Produk ini sudah memiliki promo yang akan berlangsung', 422);
        }
    
        $persenPromo = ($product->price - $request->after_promo) / $product->price * 100;
    
        // Pastikan nilai persen_promo selalu berada dalam rentang 0 hingga 100
        $persenPromo = max(0, min(100, $persenPromo));
    
        // Bulatkan nilai persen_promo ke bilangan genap terdekat
        $persenPromo = round($persenPromo);
    
        // Ubah persen_promo menjadi bilangan bulat tanpa desimal
        $persenPromo = intval($persenPromo);
    
        // Mengubah status promosi menjadi 'inactive' jika tanggal awalnya kurang dari tanggal saat ini
        $start_date = Carbon::parse($request->start_date)->format('Y-m-d');
        $today = Carbon::today()->format('Y-m-d');
        $status = ($start_date < $today) ? 'inactive' : 'active';
    
        $promo = Promo::create([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'product_id' => $request->product_id,
            'after_promo' => $request->after_promo,
            'persen_promo' => $persenPromo,
            'status' => $status,
        ]);
        // Load the 'product' relationship for the created promo
         $promo->load('product');
        
        $activePromos = Promo::where('status', 'active')->get();
        $users = User::all(); // Fetch all users
        $promosArray = [];
        
        // Loop melalui data promosi dan tambahkan ke dalam array jika "product_id" sesuai dengan permintaan
        foreach ($activePromos as $activePromo) {
            if ($activePromo->product_id == $request->product_id) {
                $promoItem = [
                    'id' => $activePromo->id,
                    'start_date' => $activePromo->start_date,
                    'end_date' => $activePromo->end_date,
                    'product_id' => $activePromo->product_id,
                    'after_promo' => $activePromo->after_promo,
                    'persen_promo' => $activePromo->persen_promo,
                    'created_at' => $activePromo->created_at,
                    'updated_at' => $activePromo->updated_at,
                    'status' => $activePromo->status,
                    'product' => $activePromo->product, // Tambahkan relasi product ke dalam array
                    // 'store_id' => $activePromo->product->store_id // Tambahkan store_id dari produk terkait
                    // Anda juga bisa menambahkan informasi produk atau store lainnya sesuai kebutuhan
                ];
    
                // Tambahkan item promosi ke dalam array utama
                $promosArray[] = $promoItem;
            }
        }

    
        // Lanjutkan dengan respons atau tindakan lainnya setelah perulangan
        return ResponseFormatter::success($promosArray, 'Berhasil mengirim notifikasi promo.');
    }



    public function update(Request $request, $id)
    {
        $promo = Promo::find($id);

        if (!$promo) {
            return ResponseFormatter::error(null, 'Data promo tidak ditemukan', 404);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
            'product_id' => 'exists:products,id',
            'after_promo' => 'numeric',
            'persen_promo' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        // Jika ada perubahan pada 'after_promo', hitung kembali nilai 'persen_promo'
        if ($request->has('after_promo')) {
            $product = Product::find($request->product_id);

            // Pastikan nilai after_promo tidak melebihi harga produk
            if ($request->after_promo > $product->price) {
                return ResponseFormatter::error(null, 'Harga setelah promo tidak boleh melebihi harga produk', 422);
            }

            $persenPromo = ($product->price - $request->after_promo) / $product->price * 100;

            // Pastikan nilai persen_promo selalu berada dalam rentang 0 hingga 100
            $persenPromo = max(0, min(100, $persenPromo));

            // Bulatkan nilai persen_promo ke bilangan genap terdekat
            $persenPromo = round($persenPromo);

            // Ubah persen_promo menjadi bilangan bulat tanpa desimal
            $persenPromo = intval($persenPromo);

            // Tambahkan nilai persen_promo ke dalam $request agar ikut terupdate di database
            $request->merge(['persen_promo' => $persenPromo]);
        }

        $promo->update($request->all());

        return ResponseFormatter::success($promo, 'Promo updated successfully');
    }

    public function destroy($id)
    {
        $promo = Promo::find($id);

        if (!$promo) {
            return ResponseFormatter::error(null, 'Data promo tidak ditemukan', 404);
        }

        $promo->delete();

        return ResponseFormatter::success(null, 'Promo deleted successfully');
    }

        public function active(Request $request)
    {
        // Cek apakah ada status yang diberikan dalam permintaan
        $status = $request->input('status', 'active');
    
        // Validasi status yang diberikan
        if ($status !== 'active' && $status !== 'inactive') {
            return ResponseFormatter::error(null, 'Invalid status provided', 400);
        }
    
        // Ambil data promosi berdasarkan status yang diberikan dalam permintaan
        $query = Promo::where('status', $status);
    
        // Filter berdasarkan id jika ada dalam permintaan
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }
    
        // Filter berdasarkan products_id jika ada dalam permintaan
        if ($request->has('products_id')) {
            $query->where('product_id', $request->products_id);
        }
    
       // Filter berdasarkan store_id jika ada dalam permintaan
        if ($request->has('store_id')) {
            $query->whereHas('product', function ($query) use ($request) {
                $query->where('store_id', $request->store_id);
            });
        }
    
        $promos = $query->get();
    
        // Jika data promosi tidak ditemukan, kembalikan respons dengan pesan error
        if ($promos->isEmpty()) {
            return ResponseFormatter::success([], 'No ' . $status . ' promos found.');
        }
    
        // Buat array baru untuk menyimpan data promosi dengan id yang sesuai dengan kebutuhan
        $formattedPromos = [];
        foreach ($promos as $promo) {
            $formattedPromos[] = [
                'promo_id' => $promo->id,
                'start_date' => $promo->start_date,
                'end_date' => $promo->end_date,
                'product_id' => $promo->product_id,
                'after_promo' => $promo->after_promo,
                'persen_promo' => $promo->persen_promo,
                'created_at' => $promo->created_at,
                'updated_at' => $promo->updated_at,
                'status' => $promo->status,
                // Jika Anda ingin menambahkan relasi product, tambahkan kolom product ke array ini
                'product' => $promo->product,
                // Jika Anda ingin menambahkan store_id, tambahkan kolom store_id ke array ini
                // 'store_id' => $promo->store_id,
            ];
        }
    
        // Kembalikan respons sukses dengan data promosi yang sudah diformat
        return ResponseFormatter::success($formattedPromos, 'List of ' . $status . ' promos');
    }

}
