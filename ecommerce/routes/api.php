<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\PromoController;
use App\Http\Controllers\API\SelectedProductController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\StoreController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\API\ChatifyController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ShippingController;
use App\Http\Controllers\API\MidtransController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\GalleryEventController;
use App\Http\Controllers\API\GalleryProductController;
use App\Http\Controllers\API\GalleryReviewController;
use App\Http\Controllers\API\BannerController;
use App\Http\Controllers\API\ProductCategoryController;
use App\Http\Controllers\API\UserAddressController;
use App\Http\Controllers\API\TransactionItemController;
use App\Http\Controllers\API\CategoryPromoController;
use App\Http\Controllers\API\ReviewStoreController;
use App\Http\Controllers\API\FollowStoreController;
use App\Http\Controllers\API\ReviewLabelController;
use App\Http\Middleware\Cors;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(Cors::class)->group(function () {

    Route::get('email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])->name('verification.email.verify');
    Route::post('/resend-verification-email', [UserController::class, 'resendVerificationEmail']);

    Route::get('/forgot-password', [UserController::class, 'forgotPassword']);
    // Endpoint untuk menambahkan jam operasional baru pada toko
    Route::post('/store/{id}/add-operating-hours', [StoreController::class, 'addOperatingHours']);

    // Endpoint untuk memperbarui jam operasional pada toko berdasarkan indeks (index) jam operasional
    Route::put('/store/{id}/update-operating-hours/{dayId}', [StoreController::class, 'updateOperatingHours']);

    Route::delete('/store/{storeId}/operational_days/{operationalDayId}', [StoreController::class, 'deleteOperationalDay']);
    // Endpoint untuk menghapus jam operasional pada toko berdasarkan indeks (index) jam operasional
    Route::get('/store/all-operating-hours', [StoreController::class, 'getAllOperatingHours']);

    Route::get('/active', [PromoController::class, 'active']);


    Route::get('products', [ProductController::class, 'all']);
    Route::get('events', [EventController::class, 'all']);
    Route::get('categories', [ProductCategoryController::class, 'all']);
    Route::get('/stores', [StoreController::class, 'getAllStores']);


    // Route::get('/provinces', [LocationController::class, 'provinces']);
    // Route::get('/regencies/{provinces_id}', [LocationController::class, 'regencies']);
    Route::get('/districts', [LocationController::class, 'districts']);
    Route::get('/village', [LocationController::class, 'village']);

    Route::get('/provinces', [LocationController::class, 'getProvinces']);
    Route::get('/cities', [LocationController::class, 'getCities']);

    Route::post('register', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'login']);


    Route::post('midtrans/callback', [TransactionController::class, 'callback']);
    Route::get('midtrans/check-payment-status/{orderId}', [TransactionController::class, 'checkPaymentStatus']);
    Route::get('/auth/google/redirect', [UserController::class, 'googleRedirect']);
    Route::get('/auth/google/callback', [UserController::class, 'googleCallback']);

    Route::get('reviews/products', [ReviewController::class, 'getProductReviews']);



    Route::group(['prefix' => 'banners'], function () {
        Route::get('/', [BannerController::class, 'index']);
        Route::get('/{id}', [BannerController::class, 'show']);
    });

    Route::group(['prefix' => 'selected'], function () {
        Route::get('/all', [SelectedProductController::class, 'getAllSelectedProduct']);
    });


    Route::middleware('auth:sanctum')->group(function () {

        // Grup untuk pengguna
        Route::prefix('user')->group(function () {
            Route::get('/', [UserController::class, 'fetch']);
            Route::put('/update-role', [UserController::class, 'updateRole']);
            Route::post('/', [UserController::class, 'updateProfile']);
            Route::post('/photo', [UserController::class, 'updatePhoto']);
            Route::get('/store', [StoreController::class, 'getAuthenticatedUserStore']);
        });

        Route::post('logout', [UserController::class, 'logout']);
        Route::post('/stores', [StoreController::class, 'store']);

        // Grup untuk transaksi
        Route::prefix('transactions')->group(function () {
            Route::get('/', [TransactionController::class, 'all']);
            Route::post('/{id}', [TransactionController::class, 'update']);
            Route::get('/{transactionId}/accept', [TransactionController::class, 'acceptOrder'])->name('transactions.accept');
            Route::get('shipped/{id}', [TransactionController::class, 'markAsShipped'])->name('transactions.shipped');
            Route::get('finished/{id}', [TransactionController::class, 'markAsFinished'])->name('transactions.finished');
        });

        Route::post('checkout', [TransactionController::class, 'checkout']);
        Route::get('/shipping/cost/{store_id}', [ShippingController::class, 'checkShippingCost']);


        Route::prefix('products')->group(function () {
            Route::post('/post', [ProductController::class, 'postProduct']);
            Route::post('/edit/{id}', [ProductController::class, 'editProduct']);
            Route::post('/{id}/updatePhoto', [ProductController::class, 'updatePhoto']);
            Route::delete('/{id}', [ProductController::class, 'deleteProduct']);
        });


        // Grup untuk galeri produk
        Route::prefix('product')->group(function () {
            Route::post('/img', [GalleryProductController::class, 'addGallery']);
            Route::get('/img', [GalleryProductController::class, 'getAllGallery']);
            Route::delete('/img/{id}', [GalleryProductController::class, 'deleteGallery']);
        });



        // Grup untuk kategori produk
        Route::prefix('categories')->group(function () {
            Route::post('/', [ProductCategoryController::class, 'postCategory']);
            Route::post('/{id}', [ProductCategoryController::class, 'editCategory']);
            Route::delete('/{id}', [ProductCategoryController::class, 'deleteCategory']);
        });


        // Grup untuk toko
        Route::prefix('stores')->group(function () {
            Route::post('/{id}', [StoreController::class, 'updateStore']);
            Route::delete('/{id}', [StoreController::class, 'deleteStore']);
        });

        Route::prefix('events')->group(function () {
            Route::post('/', [EventController::class, 'create_event']);
            Route::post('/{id}', [EventController::class, 'update_event']);
            Route::delete('/{id}', [EventController::class, 'delete_event']);
            Route::post('/{event_id}/register-send-invoice', [EventController::class, 'registerAndSendInvoice']);
            Route::get('/{event_id}/registration-status', [EventController::class, 'checkRegistrationStatus']);
            Route::get('/{event_id}/registered-users', [EventController::class, 'getRegisteredUsers']);
        });


        // Grup untuk galeri event
        Route::prefix('event')->group(function () {
            Route::post('/img', [GalleryEventController::class, 'addGallery']);
            Route::get('/img', [GalleryEventController::class, 'getAllGallery']);
            Route::get('/img/{id}', [GalleryEventController::class, 'getAllGallery']);
            Route::post('/img/{id}', [GalleryEventController::class, 'editGallery']);
            Route::delete('/img/{id}', [GalleryEventController::class, 'deleteGallery']);
        });


        // Grup untuk Chatify
        Route::prefix('chatify')->group(function () {
            Route::get('/messages', [ChatifyController::class, 'getMessages']);
            Route::post('/messages', [ChatifyController::class, 'sendMessage']);
            Route::get('/users/{user_id}', [ChatifyController::class, 'getChattedUsers']);
            Route::post('/users/{user_id}/start-chat', [ChatifyController::class, 'startChat']);
            Route::get('/users/{other_user_id}/messages', [ChatifyController::class, 'getChatMessages']);
            Route::delete('/users/{other_user_id}/delete', [ChatifyController::class, 'deleteAllChatMessages']);
            Route::delete('/messages/{id}', [ChatifyController::class, 'deleteChat']);
        });


        // Grup untuk keranjang
        Route::prefix('cart')->group(function () {
            Route::post('/add', [CartController::class, 'addToCart']);
            Route::post('/remove', [CartController::class, 'removeFromCart']);
            Route::delete('/delete/{userId}', [CartController::class, 'deleteCart']);
            Route::get('/items', [CartController::class, 'getAllCartItems']);
        });


        // Grup untuk reviews
        Route::prefix('reviews')->group(function () {
            Route::post('/', [ReviewController::class, 'store']);
            Route::put('/{id}', [ReviewController::class, 'update']);

            Route::delete('/{id}', [ReviewController::class, 'delete']);
        });

        // Grup untuk gallery-reviews
        Route::prefix('gallery-reviews')->group(function () {
            Route::post('/{review}/upload-image', [GalleryReviewController::class, 'uploadImage']);
            Route::get('/{reviewId}', [GalleryReviewController::class, 'getGalleryReview']);
            Route::put('/{id}', [GalleryReviewController::class, 'updateGalleryReview']);
            Route::delete('/{id}', [GalleryReviewController::class, 'deleteGalleryReview']);
        });

        // Grup untuk wishlist
        Route::group(['prefix' => 'wishlist'], function () {
            Route::post('/add', [WishlistController::class, 'addToWishlist']);
            Route::delete('/delete/{id}', [WishlistController::class, 'deleteFromWishlist']);
            Route::post('/filter', [WishlistController::class, 'filterByCategoryAndAvailability']);
            Route::get('/all', [WishlistController::class, 'getAllWishlist']);
        });

        Route::group(['prefix' => 'banners'], function () {
            Route::post('/', [BannerController::class, 'store']);
            Route::post('/{id}', [BannerController::class, 'update']);
            Route::delete('/{id}', [BannerController::class, 'destroy']);
        });


        // Grup untuk selectedProduct
        Route::group(['prefix' => 'selected'], function () {
            Route::post('/add', [SelectedProductController::class, 'addToSelectedProduct']);
            Route::delete('/delete/{id}', [SelectedProductController::class, 'deleteFromSelectedProduct']);
            // Route::post('/filter', [WishlistController::class, 'filterByCategoryAndAvailability']);
        });


        Route::group(['prefix' => 'user_addresses'], function () {
            Route::post('/', [UserAddressController::class, 'store']);
            Route::get('/{userId}', [UserAddressController::class, 'getByUserId']);
            Route::post('/{userAddress}', [UserAddressController::class, 'update']);
            Route::delete('/{userAddress}', [UserAddressController::class, 'destroy']);
        });

        Route::prefix('transaction_items')->group(function () {
            Route::post('/', [TransactionItemController::class, 'checkout']);
            Route::get('/detail', [TransactionItemController::class, 'getCheckoutProducts']);
        });

        Route::group(['prefix' => 'promos'], function () {
            Route::get('/', [PromoController::class, 'index']);
            Route::get('/{id}', [PromoController::class, 'show']);
            Route::post('/post', [PromoController::class, 'store']);
            Route::post('/{id}', [PromoController::class, 'update']);
            Route::delete('/{id}', [PromoController::class, 'destroy']);
        });

        Route::group(['prefix' => 'reviews_store'], function () {
            Route::get('/', [ReviewStoreController::class, 'index']);
            Route::get('/{id}', [ReviewStoreController::class, 'show']);
            Route::post('/post', [ReviewStoreController::class, 'store']);
            Route::put('/{id}', [ReviewStoreController::class, 'update']);
            Route::delete('/{id}', [ReviewStoreController::class, 'destroy']);
        });

        Route::group(['prefix' => 'category_promo'], function () {
            Route::get('/', [CategoryPromoController::class, 'index']);
            Route::post('/post', [CategoryPromoController::class, 'store']);
            Route::post('/{id}', [CategoryPromoController::class, 'update']);
            Route::delete('/{id}', [CategoryPromoController::class, 'destroy']);
        });

        // Mengikuti toko
        Route::post('/follow/{storeId}', [FollowStoreController::class, 'followStore']);

        // Berhenti mengikuti toko
        Route::delete('/unfollow/{storeId}', [FollowStoreController::class, 'unfollowStore']);

        Route::prefix('courier')->group(function () {
            Route::post('{store_id}/selected-courier', [StoreController::class, 'addCourier']);
            Route::delete('{store_id}/deleted-courier', [StoreController::class, 'removeCourier']);
            Route::get('{store_id}/selected-courier', [StoreController::class, 'getSelectedCourier']);
            Route::put('{store_id}/selected-courier', [StoreController::class, 'updateCourier']);
        });

        Route::group(['prefix' => 'review'], function () {
            Route::get('/labels', [ReviewLabelController::class, 'index']);
            Route::post('/labels', [ReviewLabelController::class, 'store']);
            Route::post('/labels/{id}', [ReviewLabelController::class, 'update']);
            Route::delete('/labels/{id}', [ReviewLabelController::class, 'destroy']);
        });
    });
});
