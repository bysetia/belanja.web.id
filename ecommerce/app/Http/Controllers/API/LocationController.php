<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Helpers\ResponseFormatter;
use App\Models\Village;
use App\Models\District;
use App\Models\Regency;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Http;
use Kavist\RajaOngkir\Facades\RajaOngkir;

class LocationController extends Controller
{
    
    public function getProvinces()
    {
        $provinces = RajaOngkir::provinsi()->all();

        return ResponseFormatter::success($provinces, 'List provinsi berhasil diambil');
        
        // $response = Http::withHeaders([
        //     'key' => 'b663776dd9ad274bef59b8bcc4019ee6'
        // ])->get('https://api.rajaongkir.com/starter/province');
        // return $response->body();
    }

    public function getCities(Request $request)
    {

        $request->validate([
            'province_id' => 'required|numeric|exists:provinces,province_id',
            'search' => 'nullable|string'
        ]);
    
        $provinceId = $request->input('province_id');
        $search = $request->input('search');
    
        $cities = RajaOngkir::kota()->dariProvinsi($provinceId)->get();
    
        if ($search) {
            $cities = array_filter($cities, function ($city) use ($search) {
                return stripos($city['city_name'], $search) !== false;
            });
            $cities = array_values($cities);
        }
    
        return ResponseFormatter::success($cities, 'List kota berhasil diambil');
    }
    
    
    //     public function provinces(Request $request)
    // {
    //     // Jika terdapat parameter "id" dalam URL, lakukan filter berdasarkan ID
    //     if ($request->has('id')) {
    //         $id = $request->id;
    //         return Province::findOrFail($id);
    //     }
        
    //         // Jika terdapat parameter "name" dalam URL, lakukan filter berdasarkan name
    //     if ($request->has('name')) {
    //         $name = $request->name;
    //         return Province::where('name', 'LIKE', '%' . $name . '%')->get();
    //     }


    
    //     // Jika tidak ada parameter "id", kembalikan semua data
    //     return Province::all();
    // }
    
    //   public function regencies(Request $request, $province_id)
    // {
    //     // Jika terdapat parameter "id" dalam URL, lakukan filter berdasarkan ID
    //     if ($request->has('id')) {
    //         $id = $request->id;
    //         return Regency::where('province_id', $province_id)->findOrFail($id);
    //     }
        
    //          // Jika terdapat parameter "name" dalam URL, lakukan filter berdasarkan name
    //     if ($request->has('name')) {
    //         $name = $request->name;
    //         return Regency::where('province_id', $province_id)->where('name', 'LIKE', '%' . $name . '%')->get();
    //     }
    
    //     // Jika tidak ada parameter "id", kembalikan data regencies berdasarkan ID province
    //     return Regency::where('province_id', $province_id)->get();
    // }
    
    public function districts(Request $request)
    {
        // Jika terdapat parameter "id" dalam URL, lakukan filter berdasarkan ID
        if ($request->has('id')) {
            $id = $request->id;
            return District::findOrFail($id);
        }
            
                // Jika terdapat parameter "name" dalam URL, lakukan filter berdasarkan name
        if ($request->has('name')) {
            $name = $request->name;
            return District::where('name', 'LIKE', '%' . $name . '%')->get();
        }
    
        // Jika tidak ada parameter "id", kembalikan semua data
        return District::all();
    }
    
    public function village(Request $request)
    {
        // Jika terdapat parameter "id" dalam URL, lakukan filter berdasarkan ID
        if ($request->has('id')) {
            $id = $request->id;
            return Village::findOrFail($id);
        }
        
              // Jika terdapat parameter "name" dalam URL, lakukan filter berdasarkan name
        if ($request->has('name')) {
            $name = $request->name;
            return Village::where('name', 'LIKE', '%' . $name . '%')->get();
        }
    
        // Jika tidak ada parameter "id", kembalikan semua data
        return Village::all();
    }
}
