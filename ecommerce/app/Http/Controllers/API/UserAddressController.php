<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAddress;
use App\Models\City;
use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Auth;

class UserAddressController extends Controller
{
    public function store(Request $request)
    {
        // Get the currently authenticated user ID
        $userId = Auth::id();

        // Validasi permintaan
        $request->validate([
            'address_one' => 'required|string',
            'provinces' => 'required|string',
            'regencies' => 'required|string',
            'zip_code' => 'required|string',
            'label_address' => 'required|string|in:Rumah,Apartemen,Kantor,Kos',
            'is_primary' => 'boolean',
            'receiver_name' => 'required|string',
            'phone_number' => 'required|string',

        ]);

        // Tambahkan user_id ke data permintaan
        $requestData = $request->all();
        $requestData['user_id'] = $userId;

        // Simpan alamat pengiriman baru
        $userAddress = UserAddress::create($requestData);


        // Tambahkan informasi "label_address" ke dalam data alamat pengiriman
        $userAddress->label_address = $request->input('label_address');

        $userAddress->load('user');

        // Ubah bentuk respons dengan mengganti indeks angka dengan string "id"
        $responseData = $userAddress->toArray();
        $responseData['id'] = $responseData['id']; // Mengganti indeks angka dengan string "id"
        unset($responseData[0]); // Menghapus indeks angka

        // Memindahkan "id" ke atas
        $responseData = array_merge(['id' => $responseData['id']], $responseData);

        return ResponseFormatter::success($responseData, 'Address added successfully.', 200);
    }

    public function update(Request $request, UserAddress $userAddress)
    {
        // Validating the request data
        $validatedData = $request->validate([
            'address_one' => 'string',
            'provinces' => 'string',
            'regencies' => 'string',
            'zip_code' => 'string',
            'label_address' => 'string|in:Rumah,Apartemen,Kantor,Kos',
            'is_primary' => 'boolean',
            'receiver_name' => 'string',
            'phone_number' => 'string',
        ]);

        // Update the user address data
        $userAddress->update($validatedData);

        // Load the associated user relationship
        $userAddress->load('user');

        // Adjust the response data
        $responseData = [
            'id' => $userAddress->id,
            'user_id' => $userAddress->user_id,
            'address_one' => $userAddress->address_one,
            'provinces' => $userAddress->provinces,
            'regencies' => $userAddress->regencies,
            'zip_code' => $userAddress->zip_code,
            'label_address' => $userAddress->label_address,
            'is_primary' => $userAddress->is_primary,
            'receiver_name' => $userAddress->receiver_name,
            'phone_number' => $userAddress->phone_number,
            // ... Include other fields you need in the response
        ];

        return ResponseFormatter::success($responseData, 'Address updated successfully.', 200);
    }

    public function destroy($id)
    {
        $userAddress = UserAddress::find($id);

        if (!$userAddress) {
            return ResponseFormatter::error('Address not found.', 404);
        }

        $userAddress->delete();

        return ResponseFormatter::success(null, 'Address deleted successfully.', 200);
    }

    public function getByUserId(Request $request, $userId)
    {
        $id = $request->input('id');

        $query = UserAddress::where('user_id', $userId);

        if ($id) {
            $query->where('id', $id);
        }

        $userAddresses = $query->orderByDesc('is_primary')->get();

        $responseData = [];
        foreach ($userAddresses as $userAddress) {
            $addressData = $userAddress->toArray();

            $regencyName = $userAddress->regencies;
            $cityId = $this->getCityIdByRegencyName($regencyName);

            $addressData['city_id'] = $cityId; // Set the city_id value



            $responseData[] = $addressData;
        }

        return ResponseFormatter::success($responseData, 'Alamat pengiriman berhasil diambil', ['regency_id']);
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
}
