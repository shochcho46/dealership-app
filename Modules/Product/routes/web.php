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
use Modules\Product\Http\Controllers\InvestorController;
use Modules\Product\Http\Controllers\AssetController;
use Modules\Product\Http\Controllers\ProfitDistributeController;
use Modules\Product\Http\Controllers\ProfitDisbursementController;
use Modules\Product\Http\Controllers\BankController;
use Modules\Product\Http\Controllers\BankAccountDetailController;
use Modules\Product\Http\Controllers\FinancialReportController;
use Modules\Product\Http\Controllers\CapitalOverviewController;
use Modules\Product\Http\Controllers\CompanyOrderController;
use Modules\Product\Http\Controllers\InspectionController;

// Public vendor account route (outside admin middleware)
Route::get('vendor-account/{uuid}', [VendorController::class, 'vendorPublicAccount'])->name('vendor.publicAccount');

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
            Route::get('vendor/export', 'export')->name('admin.vendorExport');
            Route::get('vendor/create', 'create')->name('admin.vendorCreate');
            Route::post('vendor/store', 'store')->name('admin.vendorStore');
            Route::get('vendor/{vendor}/edit', 'edit')->name('admin.vendorEdit');
            Route::put('vendor/{vendor}/update', 'update')->name('admin.vendorUpdate');
            Route::delete('vendor/{vendor}/delete', 'destroy')->name('admin.vendorDestroy');
            Route::get('vendor/{uuid}/account', 'account')->name('admin.vendorAccount');
            Route::post('vendor/add/money', 'storeVendorAccount')->name('admin.storeVendorAccount');
            Route::delete('vendor/delete/{vendorAccount}/money', 'destroyVendorAccount')->name('admin.destroyVendorAccount');
            Route::get('vendor/all-fixed', 'getVendorallFixed')->name('admin.getVendorallFixed');
            Route::get('vendor/analysis', 'vendorAnalysis')->name('admin.vendorAnalysis');
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

        // Company Order routes
        Route::controller(CompanyOrderController::class)->group(function () {
            Route::get('company-order/index', 'index')->name('admin.companyOrderIndex');
            Route::get('company-order/create', 'create')->name('admin.companyOrderCreate');
            Route::post('company-order/store', 'store')->name('admin.companyOrderStore');
            Route::get('company-order/{companyOrder}/show', 'show')->name('admin.companyOrderShow');
            Route::get('company-order/{companyOrder}/edit', 'edit')->name('admin.companyOrderEdit');
            Route::put('company-order/{companyOrder}/update', 'update')->name('admin.companyOrderUpdate');
            Route::delete('company-order/{companyOrder}/delete', 'destroy')->name('admin.companyOrderDestroy');
            Route::get('company-order/products/{companyId}', 'getProductsByCompany')->name('admin.companyOrderProducts');
            Route::post('company-order/{companyOrder}/payment', 'addPayment')->name('admin.companyOrderAddPayment');
            Route::put('company-order/{companyOrder}/payment/{payment}', 'updatePayment')->name('admin.companyOrderUpdatePayment');
            Route::delete('company-order/{companyOrder}/payment/{payment}', 'deletePayment')->name('admin.companyOrderDeletePayment');
            Route::put('company-order/{companyOrder}/item/{item}/damage-lost', 'updateItemDamageLost')->name('admin.companyOrderItemDamageLost');
            Route::put('company-order/{companyOrder}/status', 'updateStatus')->name('admin.companyOrderUpdateStatus');
            Route::get('company-order/{companyOrder}/pdf', 'generatePdf')->name('admin.companyOrderPdf');
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

        // Investor routes
        Route::controller(InvestorController::class)->group(function () {
            Route::get('investor/index', 'index')->name('admin.investorIndex');
            Route::get('investor/create', 'create')->name('admin.investorCreate');
            Route::post('investor/store', 'store')->name('admin.investorStore');
            Route::get('investor/{investor}/edit', 'edit')->name('admin.investorEdit');
            Route::put('investor/{investor}/update', 'update')->name('admin.investorUpdate');
            Route::put('investor/{investor}/status-update', 'updateStatus')->name('admin.investorStatusUpdate');
            Route::delete('investor/{investor}/delete', 'destroy')->name('admin.investorDestroy');
            Route::post('investor/{investor}/investment/store', 'storeInvestment')->name('admin.investorInvestmentStore');
            Route::delete('investment/{investment}/delete', 'destroyInvestment')->name('admin.investorInvestmentDestroy');
            Route::get('investor/{investor}/investments', 'showInvestments')->name('admin.investorInvestments');
        });

        // Asset routes
        Route::controller(AssetController::class)->group(function () {
            Route::get('asset/index', 'index')->name('admin.assetIndex');
            Route::get('asset/create', 'create')->name('admin.assetCreate');
            Route::post('asset/store', 'store')->name('admin.assetStore');
            Route::get('asset/{asset}/edit', 'edit')->name('admin.assetEdit');
            Route::put('asset/{asset}/update', 'update')->name('admin.assetUpdate');
            Route::delete('asset/{asset}/delete', 'destroy')->name('admin.assetDestroy');
        });

        // Profit Distribute routes
        Route::controller(ProfitDistributeController::class)->group(function () {
            Route::get('profit-distribute/index', 'index')->name('admin.profitDistributeIndex');
            Route::get('profit-distribute/create', 'create')->name('admin.profitDistributeCreate');
            Route::post('profit-distribute/store', 'store')->name('admin.profitDistributeStore');
            Route::get('profit-distribute/{profitDistribute}/edit', 'edit')->name('admin.profitDistributeEdit');
            Route::put('profit-distribute/{profitDistribute}/update', 'update')->name('admin.profitDistributeUpdate');
            Route::delete('profit-distribute/{profitDistribute}/delete', 'destroy')->name('admin.profitDistributeDestroy');
            Route::get('profit-distribute/{profitDistribute}/details', 'showDetails')->name('admin.profitDistributeDetails');
            Route::post('profit-distribute/{profitDistribute}/detail/store', 'storeDetail')->name('admin.profitDistributeDetailStore');
            Route::delete('profit-distribute-detail/{detail}/delete', 'destroyDetail')->name('admin.profitDistributeDetailDestroy');
        });

        // Profit Disbursement routes
        Route::controller(ProfitDisbursementController::class)->group(function () {
            Route::get('profit-disbursement/index', 'index')->name('admin.profitDisbursementIndex');
            Route::get('profit-disbursement/create', 'create')->name('admin.profitDisbursementCreate');
            Route::post('profit-disbursement/store', 'store')->name('admin.profitDisbursementStore');
            Route::get('profit-disbursement/{profitDisbursement}/edit', 'edit')->name('admin.profitDisbursementEdit');
            Route::put('profit-disbursement/{profitDisbursement}/update', 'update')->name('admin.profitDisbursementUpdate');
            Route::delete('profit-disbursement/{profitDisbursement}/delete', 'destroy')->name('admin.profitDisbursementDestroy');
        });

        // Report routes
        Route::controller(ReportController::class)->group(function () {
            Route::get('report/stock-overview', 'stockOverview')->name('admin.reportStockOverview');
            Route::get('report/order-report', 'orderReport')->name('admin.reportOrderReport');
            Route::get('report/profit-order-report', 'orderProfitReport')->name('admin.reportOrderProfitReport');
            Route::get('report/collection', 'collectionReport')->name('admin.reportCollection');
            Route::get('report/sell-summary', 'sellSummary')->name('admin.reportSellSummary');
            Route::get('report/due-orders-list', 'dueOrdersList')->name('admin.reportDueOrdersList');
            Route::get('report/profitable-product', 'profitableProduct')->name('admin.reportProfitableProduct');
        });

        // Inspection routes
        Route::controller(InspectionController::class)->group(function () {
            Route::get('inspection/index', 'index')->name('admin.inspectionIndex');
            Route::get('inspection/create', 'create')->name('admin.inspectionCreate');
            Route::post('inspection/store', 'store')->name('admin.inspectionStore');
            Route::get('inspection/{inspection}/show', 'show')->name('admin.inspectionShow');
            Route::delete('inspection/{inspection}/delete', 'destroy')->name('admin.inspectionDestroy');
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

        // Bank routes
        Route::controller(BankController::class)->group(function () {
            Route::get('bank/index', 'index')->name('admin.bankIndex');
            Route::get('bank/create', 'create')->name('admin.bankCreate');
            Route::post('bank/store', 'store')->name('admin.bankStore');
            Route::get('bank/{bank}/edit', 'edit')->name('admin.bankEdit');
            Route::put('bank/{bank}/update', 'update')->name('admin.bankUpdate');
            Route::delete('bank/{bank}/delete', 'destroy')->name('admin.bankDestroy');
            Route::get('bank/{bank}/transactions', 'transactions')->name('admin.bankTransactions');
        });

        // Bank Account Detail routes
        Route::controller(BankAccountDetailController::class)->group(function () {
            Route::get('bank-transaction/index', 'index')->name('admin.bankAccountDetailIndex');
            Route::get('bank-transaction/create', 'create')->name('admin.bankAccountDetailCreate');
            Route::post('bank-transaction/store', 'store')->name('admin.bankAccountDetailStore');
            Route::get('bank-transaction/{bankAccountDetail}/edit', 'edit')->name('admin.bankAccountDetailEdit');
            Route::put('bank-transaction/{bankAccountDetail}/update', 'update')->name('admin.bankAccountDetailUpdate');
            Route::delete('bank-transaction/{bankAccountDetail}/delete', 'destroy')->name('admin.bankAccountDetailDestroy');
        });

        // Financial Report routes
        Route::controller(FinancialReportController::class)->group(function () {
            Route::get('financial-report/index', 'index')->name('admin.financialReportIndex');
            Route::get('financial-report/create', 'create')->name('admin.financialReportCreate');
            Route::post('financial-report/store', 'store')->name('admin.financialReportStore');
            Route::get('financial-report/{financialReport}/show', 'show')->name('admin.financialReportShow');
            Route::get('financial-report/{financialReport}/edit', 'edit')->name('admin.financialReportEdit');
            Route::put('financial-report/{financialReport}/update', 'update')->name('admin.financialReportUpdate');
            Route::delete('financial-report/{financialReport}/delete', 'destroy')->name('admin.financialReportDestroy');
        });

        // Capital Overview route
        Route::controller(CapitalOverviewController::class)->group(function () {
            Route::get('capital-overview', 'index')->name('admin.capitalOverview');
        });

    });
});
