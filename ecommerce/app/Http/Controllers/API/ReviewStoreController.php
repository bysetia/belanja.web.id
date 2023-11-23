<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use App\Models\ReviewStoreUser;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Auth;

class ReviewStoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    private $siteUrl;

    public function __construct()
    {
        $this->siteUrl = config('app.url');
    }

    private function addFullImagePath($galleryReviews)
    {
        foreach ($galleryReviews as &$galleryReview) {
            $galleryReview['image_path'] = $this->siteUrl . '/ecommerce/storage/app/public/' . $galleryReview['image_path'];
        }

        return $galleryReviews;
    }

    public function index(Request $request)
    {
        $id = $request->input('id');
        $user_id = $request->input('user_id');
        $store_id = $request->input('store_id');

        $reviewsQuery = ReviewStoreUser::query();

        // Eager load relationships first
        $reviewsQuery->with('user', 'store', 'gallery');

        if ($id) {
            $reviewsQuery->where('id', $id);
        }

        if ($user_id) {
            $reviewsQuery->where('user_id', $user_id);
        }

        if ($store_id) {
            $reviewsQuery->where('store_id', $store_id);
        }

        $reviewsQuery->latest();
        $reviews = $reviewsQuery->get();

        if ($reviews->isEmpty()) {
            return ResponseFormatter::error(null, 'No reviews found', 404);
        }

        // Add full image paths to gallery reviews
        foreach ($reviews as &$review) {
            $review->gallery = $this->addFullImagePath($review->gallery);
        }

        return ResponseFormatter::success($reviews, 'Reviews retrieved successfully');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'review' => 'required|string',
            'review_id' => 'required|exists:reviews,id',
        ]);

        $user = Auth::user();
        $store = Store::where('user_id', $user->id)->first();

        if (!$store) {
            return ResponseFormatter::error(null, 'Store not found for the current user', 404);
        }

        $reviewStoreUser = ReviewStoreUser::create([
            'review' => $request->review,
            'user_id' => $user->id,
            'store_id' => $store->id,
            'review_id' => $request->review_id,
        ]);

        return ResponseFormatter::success([
            'id' => $reviewStoreUser->id,
            'user_id' => $reviewStoreUser->user_id,
            'store_id' => $reviewStoreUser->store_id,
            'review_id' => $reviewStoreUser->review_id,
            'review' => $reviewStoreUser->review,
            'created_at' => $reviewStoreUser->created_at,
            'updated_at' => $reviewStoreUser->updated_at,
        ], 'Review Store User created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $review = ReviewStoreUser::find($id);

        if ($review) {
            return ResponseFormatter::success($review, 'Review retrieved successfully');
        }

        return ResponseFormatter::error(null, 'Review not found', 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'review' => 'required|string',
        ]);

        $review = ReviewStoreUser::find($id);

        if ($review) {
            $review->update([
                'review' => $request->review,
            ]);

            return ResponseFormatter::success($review, 'Review updated successfully');
        }

        return ResponseFormatter::error(null, 'Review not found', 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $review = ReviewStoreUser::find($id);

        if ($review) {
            $review->delete();
            return ResponseFormatter::success(null, 'Review deleted successfully');
        }

        return ResponseFormatter::error(null, 'Review not found', 404);
    }
}
