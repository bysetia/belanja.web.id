<?php

namespace App\Http\Controllers\API;

use App\Models\GalleryReview;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GalleryReviewController extends Controller
{
    private $siteUrl = 'http://belanja.web.test/ecommerce/';

    public function uploadImage(Request $request, $reviewId)
    {
        $request->validate([
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image_path_2' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image_path_3' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image_path_4' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('image_path')) {
            $imagePath = $request->file('image_path')->store('galleryreviews', 'public');
        } else {
            $imagePath = null;
        }

        $galleryReview = new GalleryReview();
        $galleryReview->review_id = $reviewId;
        $galleryReview->image_path = $imagePath;

        // Simpan juga image_path_2 dan image_path_3 jika ada
        if ($request->hasFile('image_path_2')) {
            $imagePath2 = $request->file('image_path_2')->store('galleryreviews', 'public');
            $galleryReview->image_path_2 = $imagePath2;
        }

        if ($request->hasFile('image_path_3')) {
            $imagePath3 = $request->file('image_path_3')->store('galleryreviews', 'public');
            $galleryReview->image_path_3 = $imagePath3;
        }

        if ($request->hasFile('image_path_4')) {
            $imagePath4 = $request->file('image_path_4')->store('galleryreviews', 'public');
            $galleryReview->image_path_4 = $imagePath4;
        }

        $galleryReview->save();

        // Mengambil review terkait dengan GalleryReview yang baru saja dibuat
        $review = $galleryReview->review;

        // Membuat URL lengkap dengan menggunakan $siteUrl dan $imagePath
        $fullImagePath = $imagePath ? $this->siteUrl . 'storage/app/public/' . $imagePath : null;

        // Membuat URL lengkap untuk image_path_2 dan image_path_3
        $fullImagePath2 = isset($imagePath2) ? $this->siteUrl . 'storage/app/public/' . $imagePath2 : null;
        $fullImagePath3 = isset($imagePath3) ? $this->siteUrl . 'storage/app/public/' . $imagePath3 : null;
        $fullImagePath4 = isset($imagePath4) ? $this->siteUrl . 'storage/app/public/' . $imagePath4 : null;

        return ResponseFormatter::success([
            'galleryReview' => [
                'id' => $galleryReview->id,
                'review_id' => $galleryReview->review_id,
                'image_path' => $fullImagePath,
                'image_path_2' => $fullImagePath2,
                'image_path_3' => $fullImagePath3,
                'image_path_4' => $fullImagePath4,
                'updated_at' => $galleryReview->updated_at,
                'created_at' => $galleryReview->created_at,
            ],
        ], 'Image uploaded successfully');
    }

    public function getGalleryReview($reviewId)
    {
        $galleryReviews = GalleryReview::where('review_id', $reviewId)->get();

        if ($galleryReviews->isEmpty()) {
            return ResponseFormatter::error(
                null,
                'Gallery reviews not found',
                404
            );
        }

        $data = [];

        foreach ($galleryReviews as $galleryReview) {
            $fullImagePath = $this->siteUrl . 'storage/app/public/' . $galleryReview->image_path;

            $fullImagePath2 = isset($galleryReview->image_path_2) ? $this->siteUrl . $galleryReview->image_path_2 : null;

            $fullImagePath3 = isset($galleryReview->image_path_3) ? $this->siteUrl . $galleryReview->image_path_3 : null;

            $fullImagePath4 = isset($galleryReview->image_path_4) ? $this->siteUrl . $galleryReview->image_path_4 : null;

            $data[] = [
                'id' => $galleryReview->id,
                'review_id' => $galleryReview->review_id,
                'image_path' => $fullImagePath,
                'image_path_2' => $fullImagePath2,
                'image_path_3' => $fullImagePath3,
                'image_path_4' => $fullImagePath4,
                'updated_at' => $galleryReview->updated_at,
                'created_at' => $galleryReview->created_at,
            ];
        }
        return ResponseFormatter::success($data, 'Gallery reviews retrieved successfully');
    }

    public function updateGalleryReview(Request $request, $id)
    {
        $galleryReview = GalleryReview::find($id);

        if (!$galleryReview) {
            return ResponseFormatter::error(
                null,
                'Gallery review not found',
                404
            );
        }

        if ($request->file('image')) {
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $imagePath = $request->file('image')->store('galleryreviews', 'public');
            $galleryReview->image_path = $imagePath;
        }

        $galleryReview->save();

        $fullImagePath = $this->siteUrl . 'storage/app/public/' . $galleryReview->image_path;

        $data = [
            'galleryReview' => [
                'id' => $galleryReview->id,
                'review_id' => $galleryReview->review_id,
                'image_path' => $fullImagePath,
                'updated_at' => $galleryReview->updated_at,
                'created_at' => $galleryReview->created_at,
            ],
        ];

        return ResponseFormatter::success(
            $data,
            'Gallery review updated successfully'
        );
    }


    public function deleteGalleryReview($id)
    {
        $galleryReview = GalleryReview::find($id);

        if (!$galleryReview) {
            return ResponseFormatter::error(
                null,
                'Gallery review not found',
                404
            );
        }

        $galleryReview->delete();

        return ResponseFormatter::success(
            null,
            'Gallery review deleted successfully'
        );
    }
}
