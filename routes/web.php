<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BargainController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('products.index'));

/*
|--------------------------------------------------------------------------
| Guest (KUET students sign up / sign in)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Authenticated marketplace
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Specific product routes must come before the {product} wildcard.
    Route::get('/products/mine', [ProductController::class, 'myProducts'])->name('products.mine');
    Route::resource('products', ProductController::class);

    // Product photos (seller of the product, or any admin).
    Route::post('/products/{product}/images', [ProductImageController::class, 'store'])->name('product-images.store');
    Route::delete('/products/{product}/images/{image}', [ProductImageController::class, 'destroy'])->name('product-images.destroy');

    // Seller manually switches listing status (available / sold / unavailable).
    Route::patch('/products/{product}/status', [ProductController::class, 'updateStatus'])->name('products.status');

    // Bargaining (bidding)
    Route::post('/products/{product}/bids', [BargainController::class, 'store'])->name('bargains.store');
    Route::post('/bids/{bargain}/accept', [BargainController::class, 'accept'])->name('bargains.accept');
    Route::post('/bids/{bargain}/reject', [BargainController::class, 'reject'])->name('bargains.reject');
    Route::post('/bids/{bargain}/reset', [BargainController::class, 'reset'])->name('bargains.reset');

    // Wishlist (manual add/remove; automated fallback handled on bid acceptance)
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/products/{product}/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/products/{product}/wishlist', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    // Ratings & reports
    Route::post('/products/{product}/ratings', [RatingController::class, 'store'])->name('ratings.store');
    Route::post('/products/{product}/reports', [ReportController::class, 'store'])->name('reports.store');

    // Transactions ("my deals")
    Route::get('/my-deals', [TransactionController::class, 'index'])->name('transactions.index');

    // Admin area (invisible router sends admins here on login; guarded by the
    // 'admin' middleware so normal users get 403 if they type the URL).
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/products', [AdminController::class, 'products'])->name('admin.products');
        Route::get('/sql-demo', [AdminController::class, 'sqlDemo'])->name('admin.sql');
    });
});
