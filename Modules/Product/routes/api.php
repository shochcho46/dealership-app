<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\Api\ProductController as ApiProductController;
use Modules\Product\Http\Controllers\Api\VendorController as ApiVendorController;
use Modules\Product\Http\Controllers\Api\OrderController as ApiOrderController;

// Protected API routes requiring authentication
Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    
    // Product search and details
    Route::get('products/search', [ApiProductController::class, 'search']);
    Route::get('products/{id}', [ApiProductController::class, 'show']);
    
    // Vendor search and details
    Route::get('vendors/search', [ApiVendorController::class, 'search']);
    Route::post('vendors', [ApiVendorController::class, 'store']);
    Route::get('vendors/{id}', [ApiVendorController::class, 'show']);
    Route::put('vendors/{id}', [ApiVendorController::class, 'update']);
    Route::post('vendors/{id}', [ApiVendorController::class, 'update']); // For form-data with _method=PUT
    
    // Order management
    Route::get('orders/by-placed-by', [ApiOrderController::class, 'getByPlacedBy']);
    Route::get('orders/{id}', [ApiOrderController::class, 'show']);
    Route::post('orders', [ApiOrderController::class, 'store']);
    Route::put('orders/{id}', [ApiOrderController::class, 'update']);
    Route::post('orders/{id}', [ApiOrderController::class, 'update']); // For form-data with _method=PUT
    Route::post('orders/{id}/cancel', [ApiOrderController::class, 'cancel']);
    
    // Legacy routes
    Route::apiResource('products', ProductController::class)->names('product');
});
