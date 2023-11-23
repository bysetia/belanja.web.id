<?php

namespace App\Http\Controllers\API;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'review' => 'required',
            'rate' => 'required|integer|min:1|max:5',
        ]);
    
        // Retrieve the authenticated user using Auth::user()
        $user = Auth::user();
    
        $reviewData = [
            'users_id' => $user->id,
            'product_id' => $request->product_id,
            'review' => $request->review,
            'rate' => $request->rate,
        ];
    
        $review = Review::create($reviewData);
    
        $review->load(['user', 'product']);
    
        $formattedCreatedAt = Carbon::parse($review->created_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i');
    
        if ($request->product_id) {
        $product = Product::find($request->product_id);
        if ($product) {
            $product->increment('review'); // Increment the "review" field
    
            // Recalculate the average rating for the product based on all reviews
            $averageRating = $product->reviews()->avg('rate');
            
            // Ensure the average rating is between 1 and 5
            $product->rate = min(5, max(1, round($averageRating, 0, PHP_ROUND_HALF_UP)));

            $product->save();
        }
    }
        
        return ResponseFormatter::success([
            'id' => $review->id,
            'user' => $review->user,
            'product' => $review->product,
            'review' => $review->review,
            'rate' => $review->rate,
            'updated_at' => $review->updated_at,
            'created_at' => $formattedCreatedAt,
        ], 'Review successfully added');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'review' => 'required',
            'rate' => 'required|integer|min:1|max:5',
        ]);

        $review = Review::find($id);

        if (!$review) {
                return ResponseFormatter::error(null, 'No reviews found', 404);
        }

        $review->update([
            'review' => $request->review,
            'rate' => $request->rate,
        ]);

        return ResponseFormatter::success($review, 'Review updated successfully');
    }

    public function delete($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return ResponseFormatter::error(null, 'No reviews found', 404);
        }

        $review->delete();

        return ResponseFormatter::success(null, 'Review successfully deleted');
    }
    

    private $siteUrl = 'http://belanja.penuhmakna.co.id/ecommerce/';

    // public function getProductReviews($productId)
    // {
    //     $reviews = Review::where('product_id', $productId)
    //         ->with(['user', 'product', 'galleryReviews'])->latest()
    //         ->get();
    
    //     if ($reviews->isEmpty()) {
    //         return ResponseFormatter::success([], 'No reviews found');
    //     }
    
    //     $formattedReviews = [];
    
    //     foreach ($reviews as $review) {
    //         $formattedUser = $review->user->toArray();
    //         $formattedProduct = $review->product->toArray();
    //         $formattedGalleryReviews = $this->addFullImagePath($review->galleryReviews->toArray());
    
    //         $formattedReviews[] = array_merge(
    //             $review->toArray(),
    //             [
    //                 'user' => $formattedUser,
    //                 'product' => $formattedProduct,
    //                 'gallery_reviews' => $formattedGalleryReviews
    //             ]
    //         );
    //     }
    
    //     return ResponseFormatter::success($formattedReviews, 'Product reviews retrieved successfully');
    // }
    
    public function getProductReviews(Request $request)
    {
        $productId = $request->input('product_id');
        $store_id = $request->input('store_id');
        $reviewId = $request->input('review_id'); // New filter for review ID
        // Add other filters if needed
    
        // Build the base query for reviews
        $reviewsQuery = Review::query();
    
        if ($productId) {
            $reviewsQuery->where('product_id', $productId);
        }
    
        if ($store_id) {
            // Assuming 'store_id' is a field in the 'products' table
            $reviewsQuery->whereHas('product', function ($query) use ($store_id) {
                $query->where('store_id', $store_id);
            });
        }
    
        if ($reviewId) {
            $reviewsQuery->where('id', $reviewId); // Applying the filter for review ID
        }
    
        // Add more filters if needed
    
        // Retrieve the reviews with related user, product, and galleryReviews
        $reviews = $reviewsQuery->with(['user', 'product', 'galleryReviews'])
            ->latest()
            ->get();
    
        // Check if reviews are empty
        if ($reviews->isEmpty()) {
            return ResponseFormatter::success([], 'No reviews found');
        }
    
        // Format the reviews for response
        $formattedReviews = [];
        foreach ($reviews as $review) {
            $formattedReview = $review->toArray();
    
            if ($review->user) {
                $formattedUser = $review->user->toArray();
                $formattedReview['user'] = $formattedUser;
            }
    
            if ($review->product) {
                $formattedProduct = $review->product->toArray();
                $formattedReview['product'] = $formattedProduct;
            }
    
            if ($review->galleryReviews) {
                $formattedGalleryReviews = $this->addFullImagePath($review->galleryReviews->toArray());
                $formattedReview['gallery_reviews'] = $formattedGalleryReviews;
            }
    
            $formattedReviews[] = $formattedReview;
        }
    
        // Update the average rating for the product
        if ($productId) {
            $product = Product::find($productId);
            if ($product) {
                $averageRating = $product->reviews()->avg('rate');
                $product->update(['rate' => $averageRating]);
            }
        }
    
        return ResponseFormatter::success($formattedReviews, 'Product reviews retrieved successfully');
    }
    
    private function addFullImagePath($galleryReviews)
    {
        foreach ($galleryReviews as &$galleryReview) {
            $galleryReview['image_path'] = $this->siteUrl . 'storage/app/public/' . $galleryReview['image_path'];
            
            if (isset($galleryReview['image_path_2'])) {
                $imagePath2 = $galleryReview['image_path_2'];
                $fullImagePath2 = $this->siteUrl . 'storage/app/public/' . $imagePath2;
                $galleryReview['image_path_2'] = $fullImagePath2;
            }
        
            if (isset($galleryReview['image_path_3'])) {
                $imagePath3 = $galleryReview['image_path_3'];
                $fullImagePath3 = $this->siteUrl . 'storage/app/public/' . $imagePath3;
                $galleryReview['image_path_3'] = $fullImagePath3;
            }
        }
    
        return $galleryReviews;
    }



}
