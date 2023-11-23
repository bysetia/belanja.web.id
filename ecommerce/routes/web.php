<?php

use App\Http\Controllers\API\MidtransController;
use App\Http\Controllers\API\UserController as APIUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventGalleryController;
use App\Http\Controllers\EventCategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ProductGalleryController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\StoreController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group(
    ['middleware' => ['auth:sanctum', 'verified']],
    function () {

        Route::name('dashboard.')->prefix('dashboard')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('index');

            Route::middleware(['admin'])->group(function () {
                Route::resource('product', ProductController::class);
                Route::resource('category', ProductCategoryController::class);
                Route::resource('product.gallery', ProductGalleryController::class)->shallow()->only([
                    'index', 'create', 'store', 'destroy'
                ]);
                Route::resource('transaction', TransactionController::class)->only([
                    'index', 'show', 'edit', 'update'
                ]);
                Route::resource('user', UserController::class)->only([
                    'index', 'edit', 'update', 'destroy'
                ]);
                Route::resource('store', StoreController::class)->only([
                    'index', 'edit', 'update', 'destroy'
                ]);
                Route::resource('event', EventController::class);
                Route::resource('event.gallery', EventGalleryController::class)->shallow()->only([
                    'index', 'create', 'store', 'destroy'
                ]);
            });
        });
    }
);
//midtrans related
Route::get('midtrans/success', [MidtransController::class, 'success']);
Route::get('midtrans/unfinish', [MidtransController::class, 'unfinish']);
Route::get('midtrans/error', [MidtransController::class, 'error']);


Route::get('/dashboard/event/{event}/registered-users', [EventController::class, 'getRegisteredUsersView'])
    ->name('dashboard.event.registered-events');


// routes/web.php
Route::get('/edit-password', [APIUserController::class, 'showEditPasswordForm'])->name('edit-password');
Route::post('/reset-password', [APIUserController::class, 'resetPassword'])->name('reset-password');

Route::get('/reset-password-success', function () {
    return view('reset-password-success');
})->name('reset-password-success');


Route::get('dashboard/user/{user}/reset-password', [UserController::class, 'showResetPasswordForm'])->name('dashboard.user.show-reset-password');
Route::post('dashboard/user/{user}/reset-password', [UserController::class, 'resetPassword'])->name('dashboard.user.reset-password');
