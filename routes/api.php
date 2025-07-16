<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\AddressController;

/**
 * Public Routes
 */
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

/**
 * Authenticated Routes
 */
Route::middleware('auth:api')->group(function () {

    // RESTful Resources
    Route::apiResource('products', ProductController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('cart', CartController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('reviews', ReviewController::class);
    Route::apiResource('banners', BannerController::class);
    Route::apiResource('coupons', CouponController::class);
    Route::apiResource('discounts', DiscountController::class);
    Route::apiResource('address', AddressController::class);

    // 🔄 Token
    Route::get('/refreshToken', [AuthController::class, 'refreshToken']);

    // 📦 Cart Extra Actions
    Route::delete('cart/clear', [CartController::class, 'clear']);
    Route::post('cart/discount', [CartController::class, 'applyDiscount']);
    Route::delete('cart/discount', [CartController::class, 'removeDiscount']);
    Route::get('cart/total', [CartController::class, 'getTotal']);

    // 📍 Address Extra Actions
    Route::put('address/{id}/set-default', [AddressController::class, 'setDefault']);
    Route::get('address/{id}', [AddressController::class, 'show']);

    // 🛒 Product Extra Filters
    Route::get('product/category/{categoryId}', [ProductController::class, 'getByCategory']);
    Route::get('product/brand/{brandId}', [ProductController::class, 'getByBrand']);

    // 📦 Order Extra Actions
    Route::put('order/{id}/tracking', [OrderController::class, 'updateTracking']);
    Route::get('order/{id}', [OrderController::class, 'show']);

    // 📝 Review Show (already in controller)
    Route::get('reviews/{id}', [ReviewController::class, 'show']);

    // 🏷️ Discount Show
    Route::get('discounts/{id}', [DiscountController::class, 'show']);
});

/**
 * Swagger Documentation
 */
Route::get('/docs', function () {
    $swaggerView = resource_path('views/vendor/l5-swagger/index.blade.php');
    if (!File::exists($swaggerView)) {
        abort(404, 'Swagger view not found.');
    }
    return view('vendor.l5-swagger.index');
});
