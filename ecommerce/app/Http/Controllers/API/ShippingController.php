<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\SelectCourier;
use App\Models\Courier;
use App\Models\Store;

class ShippingController extends Controller
{
    // public function checkShippingCost(Request $request)
    // {
    //     $request->validate([
    //         'origin_city_id' => 'required|exists:cities,city_id',
    //         'destination_city_id' => 'required|exists:cities,city_id',
    //         'weight' => 'required|numeric'
    //     ]);

    //     $courierLogos = [
    //         'POS' => 'https://2.bp.blogspot.com/-iLELZA6nUjQ/W-g16GUu5OI/AAAAAAAAE9M/v-MynN9bJmYUnnBfjlxV8suBYDkOt8e1ACLcBGAs/s1600/Pos%2BIndonesia.png',
    //         'TIKI' => 'https://1.bp.blogspot.com/-f8R0cu_WyBI/W-gsBxcEdZI/AAAAAAAAE8o/a2xyvgeAxpIz-l6ewfDgre-aQ51pQWlfgCLcBGAs/s1600/LogoTiKi%2BTitipan%2BKilat%2BVector%2BPNG%2BHD.png',
    //         'JNE' => 'https://4.bp.blogspot.com/-UgoIn-pC3H4/W-gGu4fA8_I/AAAAAAAAE8Q/6Nd06qO0FS0NxuhDakGrZ1E9xLeCTuIKACLcBGAs/s1600/JNE.png',
    //     ];

    //     $originCityId = $request->input('origin_city_id');
    //     $destinationCityId = $request->input('destination_city_id');
    //     $weight = $request->input('weight');

    //     $apiKey = 'b663776dd9ad274bef59b8bcc4019ee6'; // Ganti dengan API key Anda

    //     $couriers = ['jne', 'tiki', 'pos']; // Daftar kurir yang ingin dicek

    //     $formattedShippingCosts = [];
    //     $totalShippingCost = 0;

    //     // Inisialisasi array kosong untuk menyimpan data harga terendah dari setiap kurir
    //     $lowestShippingCosts = [];

    //     foreach ($couriers as $courier) {
    //         $response = Http::withHeaders([
    //             'key' => $apiKey
    //         ])->post('https://api.rajaongkir.com/starter/cost', [
    //             'origin' => $originCityId,
    //             'destination' => $destinationCityId,
    //             'weight' => $weight,
    //             'courier' => $courier
    //         ]);

    //         if ($response->failed()) {
    //             return ResponseFormatter::error('Failed to get shipping information', 500);
    //         }

    //         $result = $response->json();

    //         if ($result['rajaongkir']['status']['code'] !== 200) {
    //             return ResponseFormatter::error($result['rajaongkir']['status']['description'], 500);
    //         }

    //         $costs = $result['rajaongkir']['results'][0]['costs'];

    //         // Temukan harga terendah dari masing-masing grup (kurir)
    //         $lowestCost = null;
    //         $lowestService = null;
    //         $lowestEtd = null;
    //         foreach ($costs as $cost) {
    //             $costValue = $cost['cost'][0]['value'];
    //             if ($lowestCost === null || $costValue < $lowestCost) {
    //                 $lowestCost = $costValue;
    //                 $lowestService = $cost['service'];
    //                 $lowestEtd = $cost['cost'][0]['etd'];
    //             }
    //         }

    //         // Simpan data harga terendah ke dalam array $lowestShippingCosts
    //         $lowestShippingCosts[] = [
    //             'courier' => strtoupper($courier),
    //             'service' => $lowestService,
    //             'cost' => $lowestCost,
    //             'etd' => $lowestEtd,
    //             'logo' => $courierLogos[strtoupper($courier)],
    //         ];

    //         // Jumlahkan total ongkos kirim dari masing-masing kurir
    //         // $totalShippingCost += $lowestCost;
    //     }

    //     // Buat respons JSON dengan data harga terendah dari masing-masing kurir dan total harga terendah
    //     $response = [
    //         'delivery_courier' => $lowestShippingCosts,
    //         // 'total_lowest_shipping_cost' => $totalShippingCost,
    //     ];

    //     return ResponseFormatter::success($response, 'Shipment has been found');
    // }

    public function checkShippingCost(Request $request, $store_id)
    {
        $request->validate([
            'origin_city_id' => 'required|exists:cities,city_id',
            'destination_city_id' => 'required|exists:cities,city_id',
            'weight' => 'required|numeric'
        ]);

        $courierLogos = [
            'POS' => 'https://2.bp.blogspot.com/-iLELZA6nUjQ/W-g16GUu5OI/AAAAAAAAE9M/v-MynN9bJmYUnnBfjlxV8suBYDkOt8e1ACLcBGAs/s1600/Pos%2BIndonesia.png',
            'TIKI' => 'https://1.bp.blogspot.com/-f8R0cu_WyBI/W-gsBxcEdZI/AAAAAAAAE8o/a2xyvgeAxpIz-l6ewfDgre-aQ51pQWlfgCLcBGAs/s1600/LogoTiKi%2BTitipan%2BKilat%2BVector%2BPNG%2BHD.png',
            'JNE' => 'https://4.bp.blogspot.com/-UgoIn-pC3H4/W-gGu4fA8_I/AAAAAAAAE8Q/6Nd06qO0FS0NxuhDakGrZ1E9xLeCTuIKACLcBGAs/s1600/JNE.png',
        ];

        $originCityId = $request->input('origin_city_id');
        $destinationCityId = $request->input('destination_city_id');
        $weight = $request->input('weight');

        $store = Store::with('selectCourierss.couriers')->find($store_id);

        if (!$store) {
            return ResponseFormatter::error('Store not found', 404);
        }

        $selectedCouriers = $store->selectCourierss;

        $apiKey = 'b663776dd9ad274bef59b8bcc4019ee6'; // Ganti dengan API key Anda

        $lowestShippingCosts = [];

        foreach ($selectedCouriers as $selectCourier) {
            $courier = $selectCourier->courier;
            $response = Http::withHeaders([
                'key' => $apiKey
            ])->post('https://api.rajaongkir.com/starter/cost', [
                'origin' => $originCityId,
                'destination' => $destinationCityId,
                'weight' => $weight,
                'courier' => $courier->code
            ]);

            if ($response->failed()) {
                return ResponseFormatter::error('Failed to get shipping information', 500);
            }

            $result = $response->json();

            if ($result['rajaongkir']['status']['code'] !== 200) {
                return ResponseFormatter::error($result['rajaongkir']['status']['description'], 500);
            }

            $costs = $result['rajaongkir']['results'][0]['costs'];

            $lowestCost = null;
            $lowestService = null;
            $lowestEtd = null;
            foreach ($costs as $cost) {
                $costValue = $cost['cost'][0]['value'];
                if ($lowestCost === null || $costValue < $lowestCost) {
                    $lowestCost = $costValue;
                    $lowestService = $cost['service'];
                    $lowestEtd = $cost['cost'][0]['etd'];
                }
            }

            $lowestShippingCosts[] = [
                'courier' => strtoupper($courier->title),
                'service' => $lowestService,
                'cost' => $lowestCost,
                'etd' => $lowestEtd,
                'logo' => $courierLogos[strtoupper($courier->title)],
            ];
        }

        $response = [
            'delivery_courier' => $lowestShippingCosts,
        ];

        return ResponseFormatter::success($response, 'Shipment has been found');
    }

    private function getSelectedCouriersByStoreId($store_id)
    {
        $store = Store::with('selectCourierss.couriers')->find($store_id);

        if (!$store) {
            return []; // Toko tidak ditemukan, kembalikan array kosong
        }

        $selectedCouriers = $store->selectCourierss;

        $couriers = [];
        foreach ($selectedCouriers as $selectCourier) {
            $couriers[] = $selectCouriers->courier;
        }

        return $couriers;
    }
}
