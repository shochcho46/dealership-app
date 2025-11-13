# DEALERSHIP APP - QUICK REFERENCE GUIDE

## DIRECTORY QUICK MAP

```
dealership-app/
├── Modules/Product/                    ← MAIN MODULE
│   ├── app/Http/Controllers/           ← Business Logic
│   │   ├── OrderController.php         ⭐ Orders & Smart Allocation
│   │   ├── ProductController.php       ← Products
│   │   ├── StockController.php         ← Inventory
│   │   ├── DamageReturnLostController  ⭐ Damage/Return/Lost
│   │   ├── VendorController.php        ← Vendor Management
│   │   ├── PaymentCollectionController ← Payments
│   │   └── [Other Controllers]
│   ├── app/Models/                     ← Data Models
│   │   ├── Product.php                 - name, color, unit
│   │   ├── Stock.php ⭐               - inventory tracking
│   │   ├── Order.php ⭐               - orders with invoice
│   │   ├── OrderItem.php ⭐           - order line items
│   │   ├── OrderItemStock.php ⭐      - smart allocation links
│   │   ├── Vendor.php                  - suppliers/buyers
│   │   ├── VendorAccount.php           - financial tracking
│   │   ├── DamageReturnLost.php ⭐    - issue tracking
│   │   └── [Other Models]
│   ├── database/migrations/            ← Database Schema
│   │   ├── *_create_products_table
│   │   ├── *_create_stocks_table ⭐
│   │   ├── *_create_orders_table ⭐
│   │   ├── *_create_order_items_table ⭐
│   │   ├── *_create_order_item_stocks_table ⭐
│   │   └── [Other migrations]
│   └── resources/views/                ← UI Templates
│       ├── order/                      ← Order pages
│       │   ├── index.blade.php         - list orders
│       │   ├── create.blade.php        - create order form
│       │   ├── edit.blade.php          - edit order
│       │   └── show.blade.php          - order details
│       ├── stock/                      ← Stock pages
│       ├── product/                    ← Product pages
│       ├── vendor/                     ← Vendor pages
│       ├── damage-return-lost/         ← Issue pages
│       └── [Other views]
├── resources/views/layouts/            ← Base Templates
│   ├── app.blade.php ⭐              - main layout
│   ├── header.blade.php               - top navbar
│   ├── adminsidebar.blade.php         - left menu
│   └── footer.blade.php
├── routes/
│   ├── admin.php                       - admin auth routes
│   └── [Product routes in Modules/Product/routes/]
└── config/
    └── modules.php                     - module configuration
```

## KEY MODELS & RELATIONSHIPS

### Product Ecosystem
```
Product (1) ──many──> Stock (1) ──many──> OrderItemStock
                       │
                       └──> Warehouse, Color, Unit
```

### Order Processing
```
Order (1) ──many──> OrderItem (1) ──many──> OrderItemStock
  │                    │                          │
  ├──> Vendor          ├──> Product              └──> Stock
  ├──> OrderStatus     └──> DamageReturnLost
  └──> VendorAccount
```

### Financial Tracking
```
Order
  └──> VendorAccount (Type 1: Debit when shipped)
       └──> Vendor (Balance = Credits - Debits)

PaymentCollection
  └──> VendorAccount (Type 2: Credit when payment received)
```

## DATABASE TABLES CHEAT SHEET

| Table | Purpose | Key Fields |
|-------|---------|-----------|
| **products** | Product catalog | id, name, color_id, unit_id |
| **stocks** ⭐ | Inventory management | id, product_id, quantity, sold_qty, froze_qty, sell_price |
| **orders** ⭐ | Order records | id, invoice_id, vendor_id, total_amount, status_id |
| **order_items** ⭐ | Order line items | id, order_id, product_id, quantity, sell_price |
| **order_item_stocks** ⭐ | Smart allocation | id, orderitem_id, stock_id, quantity, actual_profit |
| **vendors** | Supplier/Buyer info | id, shop_name, mobile, email, status |
| **vendor_accounts** | Payment tracking | id, vendor_id, amount, type (1=debit, 2=credit) |
| **damage_return_losts** | Issue tracking | id, order_id, quantity, status (1=damage, 2=return, 3=lost) |
| **order_statuses** | Status definitions | id, name (Pending, Confirmed, Shipped, Delivered) |
| **colors** | Product colors | id, name |
| **units** | Measurement units | id, name (Meter, Box, Piece, etc.) |
| **warehouses** | Storage locations | id, name, location |

## STOCK QUANTITY FORMULA

```
Available Stock = quantity - (sold_quantity + damage_quantity + stolen_quantity + froze_quantity)

States:
- quantity: Original received
- sold_quantity: Confirmed sold/delivered
- damage_quantity: Damaged & written off
- stolen_quantity: Stolen/lost items
- froze_quantity: RESERVED for pending orders ⭐
- transfer_quantity: Transferred between warehouses
```

## ORDER STATUS FLOW

```
PENDING (1)
    ↓
PROCESSING (2)
    ↓
CONFIRMED (3) ← Order created here
    ↓
SHIPPED (4) ← froze → sold conversion
    ↓
DELIVERED (5)
    ↓
[CANCELLED (6) - can revert from any stage except 4,5,6]
```

## CONTROLLER METHOD REFERENCE

### OrderController.php

| Method | Purpose | Returns | Key Logic |
|--------|---------|---------|-----------|
| `index()` | List orders with filters | View | Pagination, filtering, summary |
| `create()` | Order form | View | Load products, vendors, statuses |
| `store()` | Create order & allocate stock | Redirect | **Smart allocation algorithm** ⭐ |
| `show()` | Order details | View | Load all relationships |
| `edit()` | Edit form (if cancellable) | View | Check canBeCancelled() |
| `update()` | Update order | Redirect | Restore & reallocate stock |
| `cancel()` | Cancel order | Redirect | Restore frozen stock |
| `cancelled()` | List cancelled orders | View | Filter by status |
| `bulkUpdateStatus()` | Update multiple orders | JSON | **Froze→Sold conversion** ⭐ |
| `getProductDetails()` | AJAX product data | JSON | Get available stocks |
| `getStockDetails()` | AJAX stock info | JSON | Available qty & price |
| `searchVendors()` | AJAX vendor search | JSON | Name/mobile/contact search |

### DamageReturnLostController.php

| Method | Purpose | Returns | Key Logic |
|--------|---------|---------|-----------|
| `index()` | List issues | View | Filter by type, date range |
| `create()` | Issue form | View | Load orders & items |
| `store()` | Record issue | Redirect | **Distribute among stocks** ⭐ |
| `show()` | Issue details | View | Display with evidence |
| `destroy()` | Delete issue | Redirect | Reverse all updates |

## ROUTES QUICK REFERENCE

```php
// Order routes (Modules/Product/routes/web.php)
admin/order/index           - OrderController@index
admin/order/create          - OrderController@create
admin/order/store           - OrderController@store
admin/order/{id}/show       - OrderController@show
admin/order/{id}/edit       - OrderController@edit
admin/order/{id}/update     - OrderController@update
admin/order/{id}/cancel     - OrderController@cancel
admin/order/cancelled       - OrderController@cancelled
admin/order/bulk-update-status - OrderController@bulkUpdateStatus (POST)

// AJAX routes
admin/order/get-product-details - AJAX: Get product with stocks
admin/order/get-stock-details   - AJAX: Get stock info
admin/order/search-vendors      - AJAX: Search vendors

// Damage/Return/Lost routes
admin/damage-return-lost/index  - DamageReturnLostController@index
admin/damage-return-lost/create - DamageReturnLostController@create
admin/damage-return-lost/store  - DamageReturnLostController@store
admin/damage-return-lost/{id}   - DamageReturnLostController@show
```

## BLADE TEMPLATE STRUCTURE

### Master Layout Hierarchy
```
resources/views/layouts/app.blade.php
├── @include('layouts.meta')      ← Meta tags
├── @include('layouts.css')       ← CSS links
├── @stack('custome-css')         ← Child CSS injected here
├── @include('layouts.header')    ← Top navbar
├── @include('layouts.adminsidebar') ← Left menu
├── @yield('content')             ← Child content injected here
├── @include('layouts.footer')    ← Footer
├── @include('layouts.js')        ← Scripts
└── @stack('custome-js')          ← Child JS injected here
```

### Child View Pattern
```blade
@extends('layouts.app')

@push('custome-css')
    <style>/* Page CSS */</style>
@endpush

@section('content')
    <!-- Page content -->
@endsection

@push('custome-js')
    <script>/* Page JS */</script>
@endpush
```

## IMPORTANT CODE PATTERNS

### Smart Stock Allocation Algorithm
```php
// OrderController::allocateStockForOrderItem()

$stocks = Stock::where('product_id', $product->id)
    ->whereRaw('quantity > (sold + damage + stolen + froze)')
    ->orderBy('sell_price', 'desc')  // ⭐ Highest price first
    ->get();

foreach ($stocks as $stock) {
    $available = $stock->quantity - $stock->sold_quantity 
                 - $stock->damage_quantity - $stock->stolen_quantity 
                 - $stock->froze_quantity;
    
    $allocate = min($remaining, $available);
    
    OrderItemStock::create([
        'orderitem_id' => $orderItem->id,
        'stock_id' => $stock->id,
        'quantity' => $allocate,
        'actual_profit' => ($sell - $purchase) * $allocate,
        // ...
    ]);
    
    $stock->froze_quantity += $allocate;  // ⭐ Reserve
    $stock->save();
    
    $remaining -= $allocate;
}
```

### Status Update with Conversion
```php
// OrderController::bulkUpdateStatus()

if (in_array($status_id, [4, 5])) {  // Shipped or Delivered
    foreach ($order->orderItems as $item) {
        foreach ($item->orderItemStocks as $stock_alloc) {
            $stock = $stock_alloc->stock;
            
            $stock->froze_quantity -= $stock_alloc->quantity;  // ⭐ Unfreeze
            $stock->sold_quantity += $stock_alloc->quantity;   // ⭐ Mark sold
            $stock->save();
        }
    }
    
    VendorAccount::create([
        'vendor_id' => $order->vendor_id,
        'amount' => $order->total_amount,
        'type' => 1,  // Debit - they owe us
        'note' => 'Order: ' . $order->invoice_id
    ]);
}
```

### Damage/Return Processing
```php
// DamageReturnLostController::store()

$record = DamageReturnLost::create([
    'order_id' => $order_id,
    'status' => ['damage'=>1, 'return'=>2, 'lost'=>3][$type],
    'quantity' => $qty,
    // ...
]);

$this->processQuantityThroughStocks($orderItem, $qty, $type);
// Distributes qty among order_item_stocks

if ($type === 'return') {
    VendorAccount::create([
        'vendor_id' => $order->vendor_id,
        'amount' => $refund_amount,
        'type' => 2,  // Credit - they get refund
    ]);
}
```

## COMMON QUERIES

```php
// Get order with all relationships
$order = Order::with([
    'vendor',
    'orderStatus',
    'orderItems.product',
    'orderItems.orderItemStocks.stock'
])->find($id);

// Calculate available stock
$available = Stock::where('product_id', $product_id)
    ->whereRaw('quantity > (sold_qty + damage_qty + stolen_qty + froze_qty)')
    ->sum('quantity');

// Get vendor balance
$balance = VendorAccount::where('vendor_id', $vendor_id)
    ->where('type', 2)  // Credits
    ->sum('amount')
    - VendorAccount::where('vendor_id', $vendor_id)
    ->where('type', 1)  // Debits
    ->sum('amount');

// Get orders for vendor
$orders = Order::where('vendor_id', $vendor_id)
    ->with(['orderStatus', 'orderItems'])
    ->get();

// Get damage records for order
$damage = DamageReturnLost::where('order_id', $order_id)
    ->where('status', 1)  // Damage only
    ->get();
```

## API ENDPOINTS (AJAX)

### Get Product Details
```
GET /admin/order/get-product-details?product_id=5
Returns: {
    product: {...},
    available_quantity: 50,
    highest_sell_price: 1000,
    stocks: [...]
}
```

### Get Stock Details
```
GET /admin/order/get-stock-details?stock_id=3
Returns: {
    stock: {...},
    available_quantity: 30,
    sell_price: 1000
}
```

### Search Vendors
```
GET /admin/order/search-vendors?query=shop%20name
Returns: [{id, shop_name, mobile, contact_person, full_address}, ...]
```

## VIEW FILE LOCATIONS

| Feature | Files |
|---------|-------|
| Order List | `product::order.index` |
| Create Order | `product::order.create` |
| Order Details | `product::order.show` |
| Edit Order | `product::order.edit` |
| Cancelled Orders | `product::order.cancelled` |
| Damage/Return/Lost | `product::damage-return-lost.*` |
| Products | `product::product.*` |
| Stock Management | `product::stock.*` |
| Vendor Management | `product::vendor.*` |
| Payment Collection | `product::payment_collection.*` |

## TROUBLESHOOTING CHECKLIST

- [ ] Order not created: Check vendor_id exists, items array not empty
- [ ] Stock not allocating: Check stock availability (quantity > sold+damage+stolen+froze)
- [ ] Wrong profit: Check purchase_price in order_item_stocks, not in order_items
- [ ] Vendor balance wrong: Verify all vendor_accounts records (type 1 vs 2)
- [ ] Can't cancel order: Check payment_status == 0 AND status != shipped/delivered/cancelled
- [ ] Damage record fails: Check quantity doesn't exceed ordered quantity
- [ ] Invoice ID missing: Check Order::boot() method for auto-generation

---

**Last Updated**: November 13, 2025
