<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\UserStoreFollower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseFormatter;

class FollowStoreController extends Controller
{
    public function followStore(Request $request, $storeId)
    {
        $store = Store::findOrFail($storeId);

        // Pastikan pengguna terotentikasi
        $user = Auth::user();

        if (!$user) {
            return ResponseFormatter::error(null, 'Anda belum terotentikasi', 401);
        }

        // Cek apakah pengguna sudah mengikuti toko
        $isFollowing = UserStoreFollower::where('user_id', $user->id)
            ->where('store_id', $store->id)
            ->exists();

        if ($isFollowing) {
            return ResponseFormatter::error(null, 'Anda sudah mengikuti toko ini', 400);
        }

        // Tambahkan relasi pengikut ke toko
        UserStoreFollower::create([
            'user_id' => $user->id,
            'store_id' => $store->id,
        ]);

        // Update kolom follower pada model Store
        $followersCount = $store->followers()->count();
        $store->followers = $followersCount;
        $store->save();

        // Format data response
        $responseData = [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'followers' => $followersCount,
        ];

        return ResponseFormatter::success($responseData, 'Berhasil mengikuti toko', 200);
    }

    public function unfollowStore(Request $request, $storeId)
    {
        $store = Store::findOrFail($storeId);

        // Pastikan pengguna terotentikasi
        $user = Auth::user();

        if (!$user) {
            return ResponseFormatter::error(null, 'Anda belum terotentikasi', 401);
        }

        // Cek apakah pengguna sudah mengikuti toko
        $isFollowing = UserStoreFollower::where('user_id', $user->id)
            ->where('store_id', $store->id)
            ->exists();

        if ($isFollowing) {
            // Hapus relasi pengikut dari toko
            UserStoreFollower::where('user_id', $user->id)
                ->where('store_id', $store->id)
                ->delete();

            // Update kolom follower pada model Store
            $store->followers = $store->followers()->count();
            $store->save();

            return ResponseFormatter::success(null, 'Berhasil berhenti mengikuti toko', 200);
        }

        return ResponseFormatter::error(null, 'Anda belum mengikuti toko ini', 400);
    }
}
