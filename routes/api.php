<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubCategoryController;
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

// ✅ Public fetch-only routes (no token needed)
Route::apiResource('products', ProductController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('brands', BrandController::class)->only(['index', 'show']);
Route::apiResource('subcategories', SubCategoryController::class)->only(['index', 'show']);

/**
 * Authenticated Routes
 */
Route::middleware('auth:api')->group(function () {

    /**
     * Full RESTful Resources (token required)
     */
    Route::apiResources([
        'products'      => ProductController::class,
        'categories'    => CategoryController::class,
        'subcategories' => SubCategoryController::class,
        'brands'        => BrandController::class,
        'cart'          => CartController::class,
        'orders'        => OrderController::class,
        'reviews'       => ReviewController::class,
        'banners'       => BannerController::class,
        'coupons'       => CouponController::class,
        'discounts'     => DiscountController::class,
        'address'       => AddressController::class,
    ]);

    /**
     * Auth Actions
     */
    Route::get('/refreshToken', [AuthController::class, 'refreshToken']);

    /**
     * Cart Extra Routes
     */
    Route::delete('cart/clear', [CartController::class, 'clear']);
    Route::post('cart/discount', [CartController::class, 'applyDiscount']);
    Route::delete('cart/discount', [CartController::class, 'removeDiscount']);
    Route::get('cart/total', [CartController::class, 'getTotal']);

    /**
     * Product Extra Routes
     */
    Route::get('product/category/{categoryId}', [ProductController::class, 'getByCategory']);
    Route::get('product/brand/{brandId}', [ProductController::class, 'getByBrand']);
    Route::put('products/{id}', [ProductController::class, 'update']);

    /**
     * Subcategory Extra Route ✅
     */
    Route::get('SubCategories/category/{categoryId}', [SubCategoryController::class, 'getByCategory']);

    /**
     * Order Extra Routes
     */
    Route::put('order/{id}/tracking', [OrderController::class, 'updateTracking']);
    Route::get('order/{id}', [OrderController::class, 'show']);

    /**
     * Address Extra Routes
     */
    Route::put('address/{id}/set-default', [AddressController::class, 'setDefault']);
    Route::get('address/{id}', [AddressController::class, 'show']);

    /**
     * Review Show
     */
    Route::get('reviews/{id}', [ReviewController::class, 'show']);

    /**
     * Discount Show
     */
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
