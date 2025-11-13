<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\StockController;
use Modules\Product\Http\Controllers\ColorController;
use Modules\Product\Http\Controllers\OrderStatusController;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\UnitController;
use Modules\Product\Http\Controllers\VendorController;
use Modules\Product\Http\Controllers\OrderController;
use Modules\Product\Http\Controllers\WarehouseController;
use Modules\Product\Http\Controllers\PaymentMethodController;
use Modules\Product\Http\Controllers\PaymentCollectionController;
use Modules\Product\Http\Controllers\InvoiceController;
use Modules\Product\Http\Controllers\DamageReturnLostController;
use Modules\Product\Http\Controllers\ExpenseHeadController;
use Modules\Product\Http\Controllers\ExpenseListController;
use Modules\Product\Http\Controllers\CompanyController;
use Modules\Product\Http\Controllers\BrandController;
use Modules\Product\Http\Controllers\ReportController;

// Admin routes for Product module management
Route::prefix('admin')->group(function () {
    Route::middleware(['auth:admin'])->group(function () {

        // Color routes
        Route::controller(ColorController::class)->group(function () {
            Route::get('color/index', 'index')->name('admin.colorIndex');
            Route::get('color/create', 'create')->name('admin.colorCreate');
            Route::post('color/store', 'store')->name('admin.colorStore');
            Route::get('color/{color}/edit', 'edit')->name('admin.colorEdit');
            Route::put('color/{color}/update', 'update')->name('admin.colorUpdate');
            Route::delete('color/{color}/delete', 'destroy')->name('admin.colorDestroy');
        });

        // Company routes
        Route::controller(CompanyController::class)->group(function () {
            Route::get('company/index', 'index')->name('admin.companyIndex');
            Route::get('company/create', 'create')->name('admin.companyCreate');
            Route::post('company/store', 'store')->name('admin.companyStore');
            Route::get('company/{company}/edit', 'edit')->name('admin.companyEdit');
            Route::put('company/{company}/update', 'update')->name('admin.companyUpdate');
            Route::delete('company/{company}/delete', 'destroy')->name('admin.companyDestroy');
        });

        // Brand routes
        Route::controller(BrandController::class)->group(function () {
            Route::get('brand/index', 'index')->name('admin.brandIndex');
            Route::get('brand/create', 'create')->name('admin.brandCreate');
            Route::post('brand/store', 'store')->name('admin.brandStore');
            Route::get('brand/{brand}/edit', 'edit')->name('admin.brandEdit');
            Route::put('brand/{brand}/update', 'update')->name('admin.brandUpdate');
            Route::delete('brand/{brand}/delete', 'destroy')->name('admin.brandDestroy');
        });

        // Unit routes
        Route::controller(UnitController::class)->group(function () {
            Route::get('unit/index', 'index')->name('admin.unitIndex');
            Route::get('unit/create', 'create')->name('admin.unitCreate');
            Route::post('unit/store', 'store')->name('admin.unitStore');
            Route::get('unit/{unit}/edit', 'edit')->name('admin.unitEdit');
            Route::put('unit/{unit}/update', 'update')->name('admin.unitUpdate');
            Route::delete('unit/{unit}/delete', 'destroy')->name('admin.unitDestroy');
        });

        // Warehouse routes
        Route::controller(WarehouseController::class)->group(function () {
            Route::get('warehouse/index', 'index')->name('admin.warehouseIndex');
            Route::get('warehouse/create', 'create')->name('admin.warehouseCreate');
            Route::post('warehouse/store', 'store')->name('admin.warehouseStore');
            Route::get('warehouse/{warehouse}/edit', 'edit')->name('admin.warehouseEdit');
            Route::put('warehouse/{warehouse}/update', 'update')->name('admin.warehouseUpdate');
            Route::delete('warehouse/{warehouse}/delete', 'destroy')->name('admin.warehouseDestroy');
        });

        // Vendor routes
        Route::controller(VendorController::class)->group(function () {
            Route::get('vendor/index', 'index')->name('admin.vendorIndex');
            Route::get('vendor/create', 'create')->name('admin.vendorCreate');
            Route::post('vendor/store', 'store')->name('admin.vendorStore');
            Route::get('vendor/{vendor}/edit', 'edit')->name('admin.vendorEdit');
            Route::put('vendor/{vendor}/update', 'update')->name('admin.vendorUpdate');
            Route::delete('vendor/{vendor}/delete', 'destroy')->name('admin.vendorDestroy');
        });

        // Order Status routes
        Route::controller(OrderStatusController::class)->group(function () {
            Route::get('order-status/index', 'index')->name('admin.orderStatusIndex');
            Route::get('order-status/create', 'create')->name('admin.orderStatusCreate');
            Route::post('order-status/store', 'store')->name('admin.orderStatusStore');
            Route::get('order-status/{orderStatus}/edit', 'edit')->name('admin.orderStatusEdit');
            Route::put('order-status/{orderStatus}/update', 'update')->name('admin.orderStatusUpdate');
            Route::delete('order-status/{orderStatus}/delete', 'destroy')->name('admin.orderStatusDestroy');
        });

        // Product routes (Admin)
        Route::controller(ProductController::class)->group(function () {
            Route::get('product/index', 'index')->name('admin.productIndex');
            Route::get('product/create', 'create')->name('admin.productCreate');
            Route::post('product/store', 'store')->name('admin.productStore');
            Route::get('product/{product}/show', 'show')->name('admin.productShow');
            Route::get('product/{product}/edit', 'edit')->name('admin.productEdit');
            Route::put('product/{product}/update', 'update')->name('admin.productUpdate');
            Route::delete('product/{product}/delete', 'destroy')->name('admin.productDestroy');
        });

        // Stock routes
        Route::controller(StockController::class)->group(function () {
            Route::get('stock/index', 'index')->name('admin.stockIndex');
            Route::get('stock/create', 'create')->name('admin.stockCreate');
            Route::post('stock/store', 'store')->name('admin.stockStore');
            Route::get('stock/{stock}/show', 'show')->name('admin.stockShow');
            Route::get('stock/{stock}/edit', 'edit')->name('admin.stockEdit');
            Route::put('stock/{stock}/update', 'update')->name('admin.stockUpdate');
            Route::delete('stock/{stock}/delete', 'destroy')->name('admin.stockDestroy');
            Route::get('stock/get-product-details', 'getProductDetails')->name('admin.stock');
            Route::get('stock/search-products', 'searchProducts')->name('admin.stock.searchProducts');
        });

        // Payment Method routes
        Route::controller(PaymentMethodController::class)->group(function () {
            Route::get('payment-method/index', 'index')->name('admin.paymentMethodIndex');
            Route::get('payment-method/create', 'create')->name('admin.paymentMethodCreate');
            Route::post('payment-method/store', 'store')->name('admin.paymentMethodStore');
            Route::get('payment-method/{paymentMethod}/edit', 'edit')->name('admin.paymentMethodEdit');
            Route::put('payment-method/{paymentMethod}/update', 'update')->name('admin.paymentMethodUpdate');
            Route::delete('payment-method/{paymentMethod}/delete', 'destroy')->name('admin.paymentMethodDestroy');
        });



        // Invoice routes
        Route::controller(InvoiceController::class)->group(function () {
            Route::get('invoice/index', 'index')->name('invoices.index');
            Route::get('invoice/{order}/generate', 'generateInvoice')->name('invoices.generate');
            Route::get('invoice/{order}/preview', 'previewInvoice')->name('invoices.preview');
            Route::post('invoice/bulk-download', 'bulkInvoices')->name('invoices.bulk');
        });

        // Payment Collection routes
        Route::controller(PaymentCollectionController::class)->group(function () {
            Route::get('payment-collection/index', 'index')->name('payment-collections.index');
            Route::get('payment-collection/create', 'create')->name('payment-collections.create');
            Route::post('payment-collection/store', 'store')->name('payment-collections.store');
            Route::get('payment-collection/{paymentCollection}/show', 'show')->name('payment-collections.show');
            Route::get('payment-collection/{paymentCollection}/edit', 'edit')->name('payment-collections.edit');
            Route::put('payment-collection/{paymentCollection}/update', 'update')->name('payment-collections.update');
            Route::delete('payment-collection/{paymentCollection}', 'destroy')->name('payment-collections.destroy');
            Route::get('payment-collection/search-vendors', 'searchVendors')->name('admin.vendors.search');
            Route::get('payment-collection/pending-orders', 'getVendorPendingOrders')->name('admin.vendors.pending-orders');
        });

        // Damage/Return/Lost routes
        Route::controller(DamageReturnLostController::class)->group(function () {
            Route::get('damage-return-lost/index', 'index')->name('damage-return-lost.index');
            Route::get('damage-return-lost/create', 'create')->name('damage-return-lost.create');
            Route::post('damage-return-lost/store', 'store')->name('damage-return-lost.store');
            Route::get('damage-return-lost/{damageReturnLost}', 'show')->name('damage-return-lost.show');
            Route::delete('damage-return-lost/{damageReturnLost}', 'destroy')->name('damage-return-lost.destroy');
            Route::get('damage-return-lost-search/orders', 'searchOrders')->name('damage-return-lost.searchOrders');
            Route::get('damage-return-lost-search/order-items', 'getOrderItems')->name('damage-return-lost.getOrderItems');
            Route::get('damage-return-lost/test', 'test')->name('damage-return-lost.test');
        });

        // Expense Head routes
        Route::controller(ExpenseHeadController::class)->group(function () {
            Route::get('expense-head/index', 'index')->name('admin.expenseHeadIndex');
            Route::get('expense-head/create', 'create')->name('admin.expenseHeadCreate');
            Route::post('expense-head/store', 'store')->name('admin.expenseHeadStore');
            Route::get('expense-head/{expenseHead}/edit', 'edit')->name('admin.expenseHeadEdit');
            Route::put('expense-head/{expenseHead}/update', 'update')->name('admin.expenseHeadUpdate');
            Route::delete('expense-head/{expenseHead}/delete', 'destroy')->name('admin.expenseHeadDestroy');
        });

        // Expense List routes
        Route::controller(ExpenseListController::class)->group(function () {
            Route::get('expense-list/index', 'index')->name('admin.expenseListIndex');
            Route::get('expense-list/create', 'create')->name('admin.expenseListCreate');
            Route::post('expense-list/store', 'store')->name('admin.expenseListStore');
            Route::get('expense-list/{expenseList}/edit', 'edit')->name('admin.expenseListEdit');
            Route::put('expense-list/{expenseList}/update', 'update')->name('admin.expenseListUpdate');
            Route::delete('expense-list/{expenseList}/delete', 'destroy')->name('admin.expenseListDestroy');
        });

        // Report routes
        Route::controller(ReportController::class)->group(function () {
            Route::get('report/stock-overview', 'stockOverview')->name('admin.reportStockOverview');
            Route::get('report/order-report', 'orderReport')->name('admin.reportOrderReport');
            Route::get('report/profitable-product', 'profitableProduct')->name('admin.reportProfitableProduct');
        });

        // Order routes
        Route::controller(OrderController::class)->group(function () {
            Route::get('order/index', 'index')->name('orders.index');
            Route::get('order/create', 'create')->name('orders.create');
            Route::post('order/store', 'store')->name('orders.store');
            Route::get('order/{order}/show', 'show')->name('orders.show');
            Route::get('order/{order}/edit', 'edit')->name('orders.edit');
            Route::put('order/{order}/update', 'update')->name('orders.update');
            Route::delete('order/{order}/cancel', 'cancel')->name('orders.cancel');
            Route::get('order/cancelled', 'cancelled')->name('orders.cancelled');

            // AJAX routes for product and stock details
            Route::get('order/get-product-details', 'getProductDetails')->name('orders.getProductDetails');
            Route::get('order/get-stock-details', 'getStockDetails')->name('orders.getStockDetails');
            Route::get('order/search-vendors', 'searchVendors')->name('orders.searchVendors');
            Route::post('order/bulk-update-status', 'bulkUpdateStatus')->name('orders.bulkUpdateStatus');
        });

    });
});
