<?php

namespace App\Http\Controllers\API;

use App\Models\Day;
use App\Models\User;
use App\Models\Store;
use App\Models\Courier;
use App\Models\City;
use App\Models\SelectCourier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    /**
     * Create a new store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'address_one' => 'nullable',
            'address_two' => 'nullable',
            'provinces' => 'nullable',
            'regencies' => 'nullable',
            'zip_code' => 'nullable',
            'country' => 'nullable',
            'logo' => 'nullable|image', // Menambahkan validasi untuk logo
        ], [
            'name.required' => 'The name field is required.',
            'logo.image' => 'The logo must be an image.',
        ]);

        // Proses upload file logo jika ada
        if ($request->hasFile('logo')) {
            // Lakukan proses upload logo
            $logo = $request->file('logo');
            $logoPath = $logo->store('public/logos');
            $logoPath = str_replace('public/', '', $logoPath);
            $logoUrl = config('app.url') . '/ecommerce/storage/app/public/' . $logoPath;
        } else {
            $logoUrl = null; // Set logoUrl menjadi null jika logo tidak ada
        }

        // Simpan toko baru dengan data logo
        $store = new Store();
        $store->name = $request->input('name');
        $store->logo = $logoUrl;
        $store->description = $request->input('description');
        $store->address_one = $request->input('address_one');
        $store->address_two = $request->input('address_two');
        $store->provinces = $request->input('provinces');
        $store->regencies = $request->input('regencies');
        $store->zip_code = $request->input('zip_code');
        $store->country = $request->input('country');
        $store->user_id = auth()->user()->id;

        if ($store->save()) {
            $store = $store->toArray();
            $store = [
                'id' => $store['id'],
                'name' => $store['name'],
                'logo' => $store['logo'],
                'description' => $store['description'],
                'user_id' => $store['user_id'],
                'address_one' => $store['address_one'],
                'address_two' => $store['address_two'],
                'provinces' => $store['provinces'],
                'regencies' => $store['regencies'],
                'zip_code' => $store['zip_code'],
                'country' => $store['country'],
                'created_at' => $store['created_at'],
                'updated_at' => $store['updated_at'],
            ];

            // Jika penyimpanan berhasil, ubah peran pengguna menjadi 'seller'
            auth()->user()->roles = 'SELLER';
            auth()->user()->save();

            $response = [
                'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Toko berhasil dibuat'
                ],
                'data' => $store
            ];

            return response()->json($response);
        } else {
            $response = [
                'meta' => [
                    'code' => 500,
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat membuat toko'
                ],
                'data' => null
            ];

            return response()->json($response, 500);
        }
    }

    public function getAllStores(Request $request)
    {
        $id = $request->input('id');
        $storeName = $request->input('store_name'); // Get the store name from the request

        $query = Store::with('user');

        if ($storeName) {
            // Add a where clause to filter by store name directly
            $query->where('name', 'like', '%' . $storeName . '%');
        }

        if ($id) {
            $query->where('id', $id);
        }

        $stores = $query->get();

        // Loop through each store and add city_id
        $responseData = [];
        foreach ($stores as $store) {
            $cityId = $this->getCityIdByRegencyName($store->regencies);

            $storeData = $store->toArray();
            $storeData['city_id'] = $cityId;

            $responseData[] = $storeData;
        }

        return ResponseFormatter::success($responseData, 'Data semua toko berhasil diambil');
    }

    private function getCityIdByRegencyName($regencyName)
    {
        $city = City::where('title', $regencyName)->first();

        if ($city) {
            return $city->city_id;
        } else {
            return null;
        }
    }


    public function updateStore(Request $request, $id)
    {
        $store = Store::find($id);

        if (!$store) {
            return ResponseFormatter::error(null, 'Data toko tidak ditemukan', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'description' => 'string',
            'address_one' => 'string',
            'address_two' => 'string',
            'provinces' => 'string',
            'regencies' => 'string',
            'zip_code' => 'string',
            'country' => 'string',
            'status' => 'string',
            'user_id' => 'integer',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        $data = $request->only(['name', 'description', 'address_one', 'address_two', 'provinces', 'regencies', 'zip_code', 'country', 'status', 'user_id']);

        // Proses upload file logo jika ada
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoPath = $logo->store('public/logos');
            $logoPath = str_replace('public/', '', $logoPath);
            $logoUrl = config('app.url') . '/ecommerce/storage/app/public/' . $logoPath;
            $data['logo'] = $logoUrl;

            // Hapus file logo lama jika ada
            if ($store->logo) {
                Storage::delete(str_replace('/storage', 'public', $store->logo));
            }
        }

        $store->update($data);

        // Load the user relation
        $store->load('user');

        return ResponseFormatter::success($store, 'Store updated successfully');
    }

    public function getAuthenticatedUserStore()
    {
        // Mendapatkan ID pengguna yang sedang login
        $userId = Auth::id();

        // Mengambil data toko berdasarkan ID pengguna
        $store = Store::where('user_id', $userId)->first();

        if ($store) {
            $response = [
                'id' => $store->id,
                'name' => $store->name,
                'logo' => $store->logo,
                'description' => $store->description,
                'address_one' => $store->address_one,
                'address_two' => $store->address_two,
                'provinces' => $store->provinces,
                'regencies' => $store->regencies,
                'zip_code' => $store->zip_code,
                'country' => $store->country,
                'status' => $store->status,
                'saldo' => $store->saldo,
                'user_id' => $store->user_id,
                'created_at' => $store->created_at,
                'updated_at' => $store->updated_at,
                'followers' => $store->followers,
                'rate' => $store->rate,
            ];

            return ResponseFormatter::success($response, 'Store data retrieved successfully', 200);
        } else {
            return ResponseFormatter::error(null, 'Store data not found', 404);
        }
    }

    public function deleteStore($id)
    {
        $store = Store::find($id);

        if (!$store) {
            return ResponseFormatter::error(null, 'Data toko tidak ditemukan', 404);
        }

        $store->delete();

        // Ubah peran (roles) pengguna menjadi "USER"
        $user = User::find($store->user_id);
        if ($user) {
            $user->roles = 'USER';
            $user->save();
        }

        return ResponseFormatter::success(null, 'Toko berhasil dihapus');
    }

    public function addOperatingHours(Request $request, $id)
    {
        $store = Store::find($id);

        if (!$store) {
            return ResponseFormatter::error(null, 'Data toko tidak ditemukan', 404);
        }

        $validator = Validator::make($request->all(), [
            'operating_hours' => 'required|array',
            'operating_hours.*.day' => 'required|string',
            'operating_hours.*.open_time' => 'required|date_format:H:i',
            'operating_hours.*.close_time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        $existingDays = $store->days->pluck('name')->toArray();

        foreach ($request->input('operating_hours') as $operatingHourData) {
            $dayName = $operatingHourData['day'];

            // Check if the day already exists in the store's operating hours
            if (in_array($dayName, $existingDays)) {
                return ResponseFormatter::error(null, 'Jam operasional untuk hari ' . $dayName . ' sudah ada', 422);
            }

            // Cari atau buat record hari berdasarkan nama harinya
            $day = Day::firstOrCreate(['name' => $dayName]);

            // Attach new operating hours to the store
            $store->days()->attach($day->id, [
                'open_time' => $operatingHourData['open_time'],
                'close_time' => $operatingHourData['close_time'],
            ]);

            // Add the day to the existing days array to prevent duplicates
            $existingDays[] = $dayName;
        }

        // Mengambil relasi hari setelah jam operasional berhasil ditambahkan
        $store->load('days');

        return ResponseFormatter::success($store, 'Jam operasional toko berhasil ditambahkan');
    }


    public function updateOperatingHours(Request $request, $id, $dayId)
    {
        $store = Store::find($id);

        if (!$store) {
            return ResponseFormatter::error(null, 'Data toko tidak ditemukan', 404);
        }

        $validator = Validator::make($request->all(), [
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        $day = Day::find($dayId);

        if (!$day) {
            return ResponseFormatter::error(null, 'Data hari tidak ditemukan', 404);
        }

        // Update jam operasional di relasi
        $store->days()->updateExistingPivot($dayId, [
            'open_time' => $request->input('open_time'),
            'close_time' => $request->input('close_time'),
        ]);

        // Muat ulang data toko untuk mengambil data terbaru dari relasi
        $store->load('days');

        return ResponseFormatter::success($store, 'Jam operasional toko berhasil diperbarui');
    }

    public function deleteOperationalDay($storeId, $operationalDayId)
    {
        $store = Store::find($storeId);

        if (!$store) {
            return ResponseFormatter::error(null, 'Data toko tidak ditemukan', 404);
        }

        // Find the operational_day in the pivot table by $operationalDayId
        $operationalDay = $store->days()->where('operational_day.id', $operationalDayId)->first();

        if (!$operationalDay) {
            return ResponseFormatter::error(null, 'Data operasional day tidak ditemukan', 404);
        }

        // Delete the data from the 'operational_day' pivot table
        $operationalDay->delete();

        return ResponseFormatter::success(null, 'Data operasional day berhasil dihapus');
    }


    public function getAllOperatingHours(Request $request)
    {
        $store_id = $request->input('store_id');

        // Mengambil semua toko beserta relasi hari dan jam operasionalnya
        $stores = Store::with('days')->when($store_id, function ($query) use ($store_id) {
            // Jika $store_id tidak null, maka ambil hanya data toko dengan id yang sesuai
            return $query->where('id', $store_id);
        })->get();

        // Format data untuk respons
        $formattedStores = [];
        foreach ($stores as $store) {
            $operatingHours = [];
            foreach ($store->days as $day) {
                $operatingHours[] = [
                    'day' => $day->name,
                    'open_time' => $day->operational_day->open_time,
                    'close_time' => $day->operational_day->close_time,
                ];
            }

            $formattedStores[] = [
                'store_id' => $store->id,
                'store_name' => $store->name,
                'operating_hours' => $operatingHours,
            ];
        }

        return ResponseFormatter::success($formattedStores, 'Data jam operasional semua toko berhasil diambil');
    }

    public function addCourier(Request $request, $store_id)
    {
        $store = Store::find($store_id);

        if (!$store) {
            return ResponseFormatter::error('Store not found', 404);
        }

        $courierIds = $request->input('courier_id');

        if (!is_array($courierIds)) {
            $courierIds = [$courierIds];
        }

        foreach ($courierIds as $courierId) {
            $request->validate([
                'courier_id' => 'required|exists:couriers,id',
            ]);

            $store->selectCouriers()->attach($courierId);
        }

        // Use subquery to get courier data
        $subQuery = SelectCourier::select('courier_id')
            ->where('store_id', $store_id);

        $responseData = $store->toArray();
        $responseData['couriers'] = Courier::whereIn('id', $subQuery)->get()->toArray();

        return ResponseFormatter::success($responseData, 'Store courier added successfully');
    }


    public function removeCourier($store_id, Request $request)
    {
        $store = Store::find($store_id);

        if (!$store) {
            return ResponseFormatter::error('Store not found', 404);
        }

        $courierName = $request->input('courier_name');

        // Cari data kurir berdasarkan nama
        $courier = Courier::where('title', $courierName)->first();

        if (!$courier) {
            return ResponseFormatter::error('Courier not found', 404);
        }

        // Hapus relasi kurir dari toko
        $store->selectCouriers()->detach($courier->id);

        return ResponseFormatter::success($store, 'Courier removed from store successfully');
    }

    public function getSelectedCourier($store_id)
    {
        $store = Store::find($store_id);

        if (!$store) {
            return ResponseFormatter::error('Store not found', 404);
        }

        // Gunakan subquery untuk mengambil daftar courier_id dari tabel select_courier
        $subQuery = SelectCourier::select('courier_id')
            ->where('store_id', $store_id);

        // Ambil data kurir dari tabel couriers yang sesuai dengan subquery
        $selectedCourier = Courier::whereIn('id', $subQuery)->get();

        if ($selectedCourier->isEmpty()) {
            return ResponseFormatter::error('Selected courier not found', 404);
        }

        return ResponseFormatter::success($selectedCourier, 'Selected courier retrieved successfully');
    }


    public function updateCourier(Request $request, $store_id)
    {
        $store = Store::find($store_id);

        if (!$store) {
            return ResponseFormatter::error('Store not found', 404);
        }

        $request->validate([
            'courier_id' => 'required|exists:couriers,id',
        ]);

        $store->selectCourier()->updateOrCreate(
            ['store_id' => $store->id],
            ['courier_id' => $request->courier_id]
        );

        return ResponseFormatter::success($store, 'Store courier updated successfully');
    }
}
