# DEALERSHIP APP - COMPLETE PROJECT ARCHITECTURE & WORKFLOW GUIDE

## TABLE OF CONTENTS
1. [Project Overview](#project-overview)
2. [Project Structure & Modules](#project-structure--modules)
3. [Core Workflow: Product Entry → Order → Return](#core-workflow-product-entry--order--return)
4. [Database Schema & Relationships](#database-schema--relationships)
5. [View Hierarchy & Template Extension](#view-hierarchy--template-extension)
6. [Controllers & Business Logic](#controllers--business-logic)
7. [Stock Management System](#stock-management-system)
8. [Complete Workflow Detailed Steps](#complete-workflow-detailed-steps)

---

## PROJECT OVERVIEW

**Project Name:** Dealership App  
**Framework:** Laravel 12 with Modular Architecture (Laravel-Modules)  
**Database:** MySQL/MariaDB  
**Auth Systems:** Admin Guard + Web Guard (Sanctum/Passport)  
**Key Libraries:**
- `nwidart/laravel-modules` - Modular structure
- `spatie/laravel-permission` - Role-based permissions
- `spatie/laravel-medialibrary` - File management
- `barryvdh/laravel-dompdf` - PDF generation
- `maatwebsite/excel` - Excel export

### Project Purpose
A dealership inventory and order management system that handles:
- Product catalog management with images
- Stock/Inventory tracking with multiple warehouses
- Order creation and management with vendors
- Damage/Return/Lost tracking and compensation
- Vendor account management and payment tracking
- Invoice generation and expense management

---

## PROJECT STRUCTURE & MODULES

### Directory Structure
```
dealership-app/
├── app/                          # Core application code
│   ├── Http/Controllers/
│   ├── Models/                   # Admin, User, Country, etc.
│   ├── Providers/
│   └── Helper/helpers.php
├── Modules/                      # Modular components
│   ├── Admin/                    # Admin module
│   ├── Product/                  # ⭐ MAIN MODULE (Focus)
│   ├── Role/                     # Role management
│   └── User/                     # User management
├── resources/views/
│   ├── layouts/
│   │   ├── app.blade.php        # Main template
│   │   ├── header.blade.php
│   │   ├── adminsidebar.blade.php
│   │   └── footer.blade.php
│   └── [other shared views]
├── routes/
│   ├── web.php
│   ├── api.php
│   ├── admin.php                # Admin routes
│   └── console.php
├── database/
│   ├── migrations/              # All migrations
│   ├── seeders/
│   └── sql/
├── config/
│   ├── modules.php              # Module configuration
│   └── [other configs]
└── public/
    └── [assets, uploads]
```

### Modules System (Laravel-Modules)
The app uses **nwidart/laravel-modules** for a modular structure where each module is self-contained:

**Module Location:** `Modules/Product/`
```
Modules/Product/
├── app/
│   ├── Http/Controllers/        # Controllers for Product module
│   ├── Models/                  # Product, Stock, Order, etc.
│   └── [other app code]
├── database/
│   ├── migrations/              # Module-specific migrations
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── views/                   # Module-specific views
│   │   ├── layouts/master.blade.php
│   │   ├── order/
│   │   ├── product/
│   │   ├── stock/
│   │   └── [other resources]
│   └── assets/
├── routes/
│   ├── web.php                  # Module routes
│   └── api.php
├── config/
│   └── config.php               # Module config
├── tests/
├── module.json                  # Module metadata
└── composer.json
```

### How Modules Work
1. **Auto-Loading**: Modules are auto-discovered and loaded via `config/modules.php`
2. **Namespacing**: `Modules\Product\` is registered in composer
3. **Views**: Accessed as `product::view-name` (e.g., `product::order.index`)
4. **Routes**: Registered automatically from `Modules/Product/routes/web.php`
5. **Migrations**: Run via `php artisan migrate`

---

## CORE WORKFLOW: PRODUCT ENTRY → ORDER → RETURN

### High-Level Process Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    DEALERSHIP APP WORKFLOW                      │
└─────────────────────────────────────────────────────────────────┘

1. SETUP PHASE
   ├── Create Colors (Optional metadata)
   ├── Create Units (Measurement units)
   ├── Create Warehouses (Storage locations)
   └── Create Vendors (Suppliers/Buyers)

2. PRODUCT ENTRY PHASE
   ├── Create Products
   │   └── Name, Color, Unit, Status
   └── Add Stock Entries
       ├── Select Product, Warehouse, Batch ID
       ├── Define Prices (Purchase, Sell)
       ├── Set Initial Quantity
       └── Stock Ready for Orders

3. ORDER CREATION PHASE
   ├── Admin creates Order
   ├── Select Vendor
   ├── Add Order Items (Products + Quantities)
   ├── SMART STOCK ALLOCATION
   │   ├── System finds available stocks
   │   ├── Allocates from highest price first (profit optimization)
   │   ├── Freezes quantities in stock
   │   └── Creates order_item_stock records
   ├── Calculate Totals (Amount, Discount, Quantity)
   └── Order Status: CONFIRMED

4. ORDER STATUS UPDATES
   ├── Status: Pending → Processing → Confirmed
   ├── Status: Confirmed → Shipped (Status ID: 4)
   │   └── Frozen Stock → Sold Stock
   │   └── Create Vendor Account record (DEBIT)
   └── Status: Shipped → Delivered (Status ID: 5)

5. DAMAGE/RETURN/LOST HANDLING
   ├── Track damaged items during delivery
   ├── Process returns from customers
   ├── Record lost items
   ├── Update:
   │   ├── order_item_stocks (damage_qty, return_qty, lost_qty)
   │   ├── Stock quantities accordingly
   │   └── Vendor account (CREDIT for return value)

6. PAYMENT COLLECTION
   ├── Collect payments from vendors
   ├── Payment Methods (Cash, Check, Bank Transfer)
   ├── Create Payment Collection records
   └── Update Vendor Account (CREDIT)

7. FINANCIAL REPORTING
   ├── Vendor Balance Calculation
   ├── Invoice Generation
   └── Expense Management
```

---

## DATABASE SCHEMA & RELATIONSHIPS

### Core Tables & Their Relationships

#### 1. **Products Table**
```
products
├── id (PK)
├── name (Product name)
├── color_id (FK → colors)
├── unit_id (FK → units)
├── measurement_unit_name (e.g., "Meter")
├── measurement_unit_number (e.g., "10")
├── package_unit_name (e.g., "Box")
├── package_unit_quantity (e.g., "50")
├── status (Boolean: Active/Inactive)
├── created_at, updated_at
└── has_many: stocks
```

#### 2. **Stocks Table** (Inventory Management)
```
stocks
├── id (PK)
├── product_id (FK → products)
├── warehouse_id (FK → warehouses)
├── batch_id (Batch/lot number)
├── purchase_price (Cost price per unit)
├── quantity (Initial quantity)
├── total_price (quantity × purchase_price)
├── sell_price (Sales price per unit)
├── damage_quantity (Damaged count)
├── sold_quantity (Sold count)
├── stolen_quantity (Stolen/missing count)
├── transfer_quantity (Transferred count)
├── froze_quantity ⭐ (FROZEN for pending orders)
├── status (Boolean: Active/Inactive)
├── created_at, updated_at
└── IMPORTANT: remaining = quantity - (sold + damage + stolen + froze)
```

**Stock Quantity States:**
- **quantity**: Original received quantity
- **sold_quantity**: Confirmed as sold/delivered
- **damage_quantity**: Damaged and written off
- **stolen_quantity**: Lost/stolen items
- **froze_quantity**: Reserved for pending orders
- **Available Qty** = quantity - (sold + damage + stolen + froze)

#### 3. **Orders Table**
```
orders
├── id (PK)
├── invoice_id (Unique: "SSE-13-11-2025-0001-1")
├── admin_id (FK → admins)
├── vendor_id (FK → vendors)
├── order_status_id (FK → order_statuses)
├── total_amount (Sum of all items)
├── paid_amount (Amount received)
├── total_quantity (Total items)
├── total_discount_amount
├── total_damage_quantity
├── total_lost_quantity
├── total_return_quantity
├── payment_status (0: Unpaid, 1: Partial, 2: Paid)
├── created_at, updated_at
├── has_many: order_items
└── belongs_to: vendor, admin, order_status
```

#### 4. **Order Items Table**
```
order_items
├── id (PK)
├── order_id (FK → orders)
├── product_id (FK → products)
├── quantity (Ordered quantity)
├── purchase_price (Average cost)
├── sell_price (Unit selling price)
├── total_price (sell_price × quantity - discount)
├── discount_price (Total discount)
├── return_quantity
├── damage_quantity
├── lost_quantity
├── created_at, updated_at
├── has_many: order_item_stocks
└── belongs_to: order, product
```

#### 5. **Order Item Stocks Table** ⭐ (Smart Allocation)
```
order_item_stocks
├── id (PK)
├── orderitem_id (FK → order_items)
├── stock_id (FK → stocks) ⭐ Links to specific stock
├── quantity (Allocated from this stock)
├── purchase_price (Cost from this stock)
├── sell_price (Sale price)
├── total_price (Calculated value)
├── discount_amount (Proportional discount)
├── actual_profit (Profit from this allocation)
├── return_quantity
├── damage_quantity
├── lost_quantity
├── created_at, updated_at
└── belongs_to: order_item, stock
```

**Purpose**: Tracks which specific stock batches are used for each order item, enabling:
- Profit calculation per allocation
- Batch tracking
- Damage/Return/Lost processing per source stock

#### 6. **Vendors Table**
```
vendors
├── id (PK)
├── uuid (UNIQUE, for external APIs)
├── shop_name
├── contact_person
├── email
├── mobile
├── country_id (FK → countries)
├── full_address
├── lat, long (Coordinates)
├── status (Active/Inactive)
├── created_at, updated_at
├── has_many: orders, vendor_accounts
└── vendor_accounts (for balance calculation)
```

#### 7. **Vendor Accounts Table** (Finance Tracking)
```
vendor_accounts
├── id (PK)
├── vendor_id (FK → vendors)
├── order_id (FK → orders)
├── payment_method_id (FK → payment_methods)
├── amount (Transaction amount)
├── type (1: Debit/Bill, 2: Credit/Payment)
├── note (Description)
├── collection_date (When payment collected)
├── created_by (Admin who recorded)
├── deposite_by (Admin who received payment)
├── created_at, updated_at
└── Balance = SUM(Credits) - SUM(Debits)
```

**Transaction Types:**
- **Type 1 (Debit)**: Order given to vendor (increases their debt)
- **Type 2 (Credit)**: Payment received from vendor (decreases their debt)

#### 8. **Damage Return Lost Table**
```
damage_return_losts
├── id (PK)
├── product_id (FK → products)
├── stock_id (FK → stocks)
├── order_id (FK → orders)
├── order_item_id (FK → order_items)
├── order_item_stock_id (FK → order_item_stocks)
├── quantity (Items affected)
├── status (1: Damage, 2: Return, 3: Lost)
├── purchase_price (Unit cost)
├── total_price (quantity × unit_price)
├── reason (Text description)
├── created_at, updated_at
├── belongs_to: order, order_item, stock
└── has_many: media (evidence pictures)
```

#### 9. **Order Statuses Table**
```
order_statuses
├── id (PK)
├── name (Status name)
├── description
├── status (Active/Inactive)
└── created_at, updated_at

Common Statuses:
├── 1: Pending
├── 2: Processing
├── 3: Confirmed
├── 4: Shipped (Froze → Sold conversion)
├── 5: Delivered
└── 6: Cancelled (Stock restored)
```

### Key Relationships Diagram

```
┌─────────────┐
│  Products   │
└──────┬──────┘
       │ (has_many)
       ↓
┌─────────────────┐         ┌──────────┐
│     Stocks      │────────→│Warehouse │
└────────┬────────┘         └──────────┘
         │ (has_many)
         ↓
┌──────────────────────┐
│  Order Item Stocks   │ ⭐ (Smart Allocation)
└────────┬─────────────┘
         │ (belongs_to)
         ↓
┌──────────────────────┐
│   Order Items        │
└────────┬─────────────┘
         │ (belongs_to)
         ↓
┌──────────────────────┐       ┌──────────────┐
│     Orders           │──────→│   Vendors    │
└────────┬─────────────┘       └──────┬───────┘
         │                            │
         │ (has_many)                 │ (has_many)
         ↓                            ↓
┌──────────────────────┐       ┌──────────────────────┐
│ Damage Return Lost   │       │ Vendor Accounts      │
└──────────────────────┘       │ (Finance Tracking)   │
                               └──────────────────────┘
```

---

## VIEW HIERARCHY & TEMPLATE EXTENSION

### Template Inheritance Chain

```
Base Application Template
└── resources/views/layouts/app.blade.php
    ├── Includes: layouts/meta.blade.php (Meta tags)
    ├── Includes: layouts/css.blade.php (CSS links)
    ├── Includes: layouts/header.blade.php (Top navbar)
    ├── Includes: layouts/adminsidebar.blade.php (Left sidebar)
    ├── Includes: layouts/footer.blade.php (Footer)
    ├── Includes: layouts/js.blade.php (JS scripts)
    ├── @stack('custome-css') ← Child views push here
    ├── @yield('content') ← Child content injected here
    └── @stack('custome-js') ← Child views push here
```

### View Structure

**Child View Example:** `Modules/Product/resources/views/order/index.blade.php`
```blade
@extends('layouts.app')              ← Extends main layout

@section('title', 'Orders')          ← Page title

@push('custome-css')                 ← Push custom CSS
    <style>
        /* Order-specific styles */
    </style>
@endpush

@section('content')                  ← Main content section
    <div class="container-fluid">
        <!-- Page header -->
        <!-- Filters -->
        <!-- Summary cards -->
        <!-- Data table with orders -->
    </div>
@endsection

@push('custome-js')                  ← Push custom JS
    <script>
        /* Order-specific scripts */
    </script>
@endpush
```

### View Organization (Product Module)

```
Modules/Product/resources/views/
├── layouts/
│   └── master.blade.php ← Module master layout (not used, uses app.blade.php)
├── order/
│   ├── index.blade.php ← Order listing page
│   ├── create.blade.php ← Create order form
│   ├── edit.blade.php ← Edit order form
│   ├── show.blade.php ← Order detail page
│   └── cancelled.blade.php ← Cancelled orders list
├── product/
│   ├── index.blade.php ← Products list
│   ├── create.blade.php ← Create product
│   ├── edit.blade.php ← Edit product
│   └── show.blade.php ← Product details
├── stock/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── [other stock views]
├── vendor/
├── damage-return-lost/
├── invoice/
├── payment_collection/
└── [other resource views]
```

### How Blade Layout System Works

**Master Layout** (`resources/views/layouts/app.blade.php`):
```blade
<html>
  <head>
      @include('layouts.meta')        ← Include meta tags
      @include('layouts.css')         ← Include CSS
      @stack('custome-css')           ← Stack for child CSS
  </head>
  <body>
      @include('layouts.header')      ← Header component
      @include('layouts.adminsidebar')← Sidebar component
      
      <main class="app-main">
          @yield('content')           ← Content placeholder
      </main>
      
      @include('layouts.footer')      ← Footer component
      @include('layouts.js')          ← JS scripts
      @stack('custome-js')            ← Stack for child JS
  </body>
</html>
```

**Child View** (`order/index.blade.php`):
```blade
@extends('layouts.app')              ← Use parent layout

@push('custome-css')
    <style>/* Custom CSS */</style>   ← Added to @stack
@endpush

@section('content')
    <!-- Actual content -->           ← Replaces @yield('content')
@endsection

@push('custome-js')
    <script>/* Custom JS */</script>   ← Added to @stack
@endpush
```

### View Rendering Process

```
1. User requests GET /admin/order/index
2. Route matches → OrderController@index
3. Controller renders: view('product::order.index', $data)
4. Blade engine resolves 'product::order.index'
   → Modules/Product/resources/views/order/index.blade.php
5. This view extends 'layouts.app'
   → resources/views/layouts/app.blade.php (MAIN)
6. Main layout includes components:
   - meta, css, header, adminsidebar, footer, js
7. Child view @section('content') replaces @yield('content')
8. Child view @stacks merged into parent @stacks
9. Final HTML sent to browser
```

---

## CONTROLLERS & BUSINESS LOGIC

### OrderController (Main Order Management)

**Location:** `Modules/Product/app/Http/Controllers/OrderController.php`

#### Key Methods

**1. index() - Display Orders List**
- Retrieves paginated orders with filters
- Shows order status summary (total orders, amount, pending, completed)
- Filters: invoice search, status, vendor, date range
- Returns: `product::order.index` view with data

**2. create() - Order Creation Form**
- Loads active products with available stocks
- Loads active vendors
- Loads active order statuses
- Returns: `product::order.create` view

**3. store() - Create Order with Smart Stock Allocation** ⭐
```
Steps:
1. Validate request data
2. Start DB transaction
3. Find or create order with status "Confirmed"
4. For each order item:
   a. Create OrderItem record
   b. Call allocateStockForOrderItem()
   c. Calculate totals
5. Update order with totals
6. Commit transaction
7. Return success message with invoice ID
```

**4. allocateStockForOrderItem() - SMART STOCK ALLOCATION** ⭐ (Core Logic)
```
Purpose: Intelligently allocate stock to orders for profit optimization

Algorithm:
1. Get all available stocks for the product
   - Filter: quantity > (sold + damage + stolen + froze)
   - Order by: sell_price DESC (highest price first - profit optimization)

2. For each available stock:
   - Calculate remaining available qty
   - Allocate min(requested, available)
   
3. For each allocation:
   - Create OrderItemStock record with:
     * Stock batch information
     * Purchase price from that batch
     * Selling price for the order
     * Calculate profit: (sell_price - purchase_price) × qty
   - Increment stock.froze_quantity
   - Track total purchase price

4. Update OrderItem with average purchase price
5. If insufficient stock → throw exception and rollback

Result: Order items are linked to specific stock batches,
        enabling batch tracking and profit calculation
```

**5. show() - Order Details Page**
- Loads order with all relationships
- Shows order items and their stock allocations
- Displays vendor and admin info
- Shows order status and payment status

**6. edit() - Edit Order Form**
- Checks if order can be edited (canBeCancelled() method)
- Loads order items and stocks
- Allows modifying items before shipment

**7. update() - Update Order**
```
Steps:
1. Check if order can be updated
2. Start transaction
3. Restore all frozen quantities from existing items
4. Delete old order items and their allocations
5. Create new order items with fresh allocations
6. Recalculate totals
7. Commit
```

**8. cancel() - Cancel Order and Restore Stock**
- Validates order can be cancelled
- Restores frozen stock quantities
- Updates order status to "Cancelled"
- Returns success message

**9. bulkUpdateStatus() - Update Multiple Orders** ⭐
```
Important: When status changes to "Shipped" (4) or "Delivered" (5):
1. Update order_status_id
2. For each order item stock:
   - stock.froze_quantity -= allocated_qty
   - stock.sold_quantity += allocated_qty
3. Create VendorAccount record (Type 1 - Debit)
   - Records the order amount as debt to vendor
```

**10. AJAX Methods - getProductDetails(), getStockDetails(), searchVendors()**
- Return JSON data for frontend autocomplete
- getProductDetails: All products or single product with stocks
- getStockDetails: Stock availability and pricing
- searchVendors: Vendor search by name/mobile/contact

### DamageReturnLostController (Return Management) ⭐

**Location:** `Modules/Product/app/Http/Controllers/DamageReturnLostController.php`

#### Key Methods

**1. index() - List All Damage/Return/Lost Records**
- Filters by type, product, date range
- Shows summary: total damaged, total lost, total value
- Links to associated orders

**2. create() - Create Damage/Return/Lost Record**
- Form to select order
- Shows order items with current statuses
- Allows selecting quantity affected
- Type selection: damage (1), return (2), lost (3)

**3. store() - Record Damage/Return/Lost** ⭐
```
Important Business Logic:

1. Validate:
   - Type: damage, return, lost
   - Quantity doesn't exceed available in order item
   
2. Create DamageReturnLost record with:
   - Type (damage=1, return=2, lost=3)
   - Quantity affected
   - Reason for damage/return
   - Unit price and total price
   - Evidence images

3. Call processQuantityThroughStocks():
   - Distribute quantity across order_item_stocks
   - Update stock with damage/return/lost quantities
   - Update order_item with respective quantities

4. Call updateOrderItemTotals():
   - Recalculate order item effective quantity
   - Update order item totals

5. Call updateOrderTotals():
   - Update order's total damage/lost/return quantities
   - Recalculate order totals

6. Call updateVendorAccount():
   - If return: Create Credit (Type 2) for vendor
     → Vendor gets credit for returned amount
   - This reduces vendor's debt

Results in adjusted stock quantities:
- damage_quantity increased (written off)
- return_quantity increased (returned to inventory)
- lost_quantity increased (unrecoverable)
```

---

## STOCK MANAGEMENT SYSTEM

### Stock Quantity Tracking

```
┌─────────────────────────────────────────────────────────┐
│             STOCK QUANTITY LIFECYCLE                    │
└─────────────────────────────────────────────────────────┘

Initial Receipt:
  quantity = 100
  sold = 0, damage = 0, stolen = 0, froze = 0
  Available = 100

After Order Creation (Frozen):
  quantity = 100
  sold = 0, damage = 0, stolen = 0, froze = 30 ← RESERVED
  Available = 100 - 30 = 70

After Order Shipped (Sold):
  quantity = 100
  sold = 30 ← CONFIRMED SOLD
  damage = 0, stolen = 0, froze = 0
  Available = 100 - 30 = 70

After Damage Reported:
  quantity = 100
  sold = 30
  damage = 5 ← DAMAGED/WRITTEN OFF
  stolen = 0, froze = 0
  Available = 100 - 30 - 5 = 65

After Return:
  quantity = 100 ← BACK IN INVENTORY
  sold = 30 - 3 ← 3 ITEMS RETURNED
  damage = 5
  stolen = 0, froze = 0
  Available = 100 - 27 - 5 = 68

After Theft/Loss:
  quantity = 100
  sold = 27
  damage = 5
  stolen = 2 ← LOST
  Available = 100 - 27 - 5 - 2 = 66
```

### Stock Allocation Strategy

The system uses **FIFO with Price Optimization**:

1. **Highest Sell Price First**
   - Allocates from stocks with highest sell price
   - Maximizes profit per transaction
   - Useful for seasonal pricing

2. **Multiple Batch Support**
   - Single order can draw from multiple stock batches
   - Each allocation tracked separately in order_item_stocks
   - Enables batch traceability

3. **Frozen Quantity Reservation**
   - When order created: froze stock immediately
   - Stock not available for other orders until:
     - Order cancelled (unfroze)
     - Order shipped (convert froze → sold)

### Available Stock Calculation

```php
// Used throughout the application
$available = $stock->quantity 
           - $stock->sold_quantity 
           - $stock->damage_quantity 
           - $stock->stolen_quantity 
           - $stock->froze_quantity;

// Or via Eloquent scope
Stock::available()->get();
```

---

## COMPLETE WORKFLOW DETAILED STEPS

### WORKFLOW 1: PRODUCT CREATION TO STOCK ENTRY

```
STEP 1: Create Product
├── Admin → Admin Panel → Products → Create
├── Enter: Name, Color, Unit, Measurement details
├── Upload product image
├── ProductController@store
└── Product created in DB

STEP 2: Add Stock to Inventory
├── Admin → Stock → Create
├── Select: Product, Warehouse, Batch ID
├── Enter: Purchase Price, Sell Price, Quantity
├── StockController@store
├── Stock record created with:
│   ├── quantity: 100 (received)
│   ├── sold_quantity: 0
│   ├── damage_quantity: 0
│   ├── froze_quantity: 0
│   └── status: Active
└── Stock ready for orders
```

### WORKFLOW 2: ORDER CREATION WITH SMART ALLOCATION

```
STEP 1: Create Order
├── Admin → Orders → Create New
├── OrderController@create()
│   ├── Loads active products
│   ├── Loads available stocks for each product
│   └── Loads vendors
└── Display create form

STEP 2: Select Vendor and Products
├── Admin selects vendor via autocomplete
│   └── SearchVendors() AJAX returns matching vendors
├── Admin adds items to order:
│   ├── Select Product via autocomplete
│   │   └── GetProductDetails() returns products with available qty
│   ├── Enter Quantity
│   ├── Enter Sell Price
│   ├── (Optional) Enter Discount
│   └── Click "Add Item"

STEP 3: Smart Stock Allocation (Automatic)
├── OrderController@store()
│   ├── Validate all inputs
│   ├── Create Order record
│   │   ├── invoice_id: "SSE-13-11-2025-0001-1" (auto-generated)
│   │   ├── vendor_id: Selected vendor
│   │   ├── order_status_id: 3 (Confirmed)
│   │   ├── payment_status: 0 (Unpaid)
│   │   └── total_amount, total_quantity: 0 (will update)
│   │
│   └── For each ordered item:
│       ├── Create OrderItem record
│       │   ├── product_id, order_id
│       │   ├── quantity: 30 items ordered
│       │   ├── sell_price: 1000 per unit
│       │   ├── total_price: 30000
│       │   └── purchase_price: 0 (will be calculated)
│       │
│       └── Call allocateStockForOrderItem(orderItem, 30)
│           │
│           ├── Find available stocks:
│           │   ├── Stock A: quantity=50, sell_price=1200, purchase_price=800
│           │   ├── Stock B: quantity=40, sell_price=1000, purchase_price=750
│           │   └── Order by sell_price DESC (A first)
│           │
│           ├── Allocate from Stock A (20 items):
│           │   ├── Create OrderItemStock
│           │   │   ├── stock_id: A
│           │   │   ├── quantity: 20
│           │   │   ├── purchase_price: 800
│           │   │   ├── sell_price: 1000
│           │   │   ├── actual_profit: (1000-800)×20 = 4000
│           │   │   └── discount_amount: proportional
│           │   │
│           │   └── Update Stock A:
│           │       └── froze_quantity: 0 → 20
│           │
│           ├── Allocate from Stock B (10 items):
│           │   ├── Create OrderItemStock
│           │   │   ├── stock_id: B
│           │   │   ├── quantity: 10
│           │   │   ├── purchase_price: 750
│           │   │   ├── sell_price: 1000
│           │   │   ├── actual_profit: (1000-750)×10 = 2500
│           │   │   └── discount_amount: proportional
│           │   │
│           │   └── Update Stock B:
│           │       └── froze_quantity: 0 → 10
│           │
│           ├── Calculate average purchase price:
│           │   └── (800×20 + 750×10) / 30 = 783.33
│           │
│           └── Update OrderItem:
│               └── purchase_price: 783.33
│
│   ├── Calculate order totals:
│   │   ├── total_amount: 30000 - discount
│   │   ├── total_quantity: 30
│   │   └── total_discount_amount: sum of discounts
│   │
│   └── Update Order with totals
│
├── Transaction committed
└── Success! Order created with Invoice ID

RESULT:
├── Order created and confirmed
├── Stock A: froze_qty = 20 (20 items unavailable for other orders)
├── Stock B: froze_qty = 10
├── OrderItemStock records created linking order to source batches
└── Admin can see order in list with invoice ID
```

### WORKFLOW 3: ORDER STATUS UPDATE → PAYMENT CREATION

```
STEP 1: Admin Marks Order as Shipped
├── Admin → Orders → Select Order
├── Click "Change Status" → Select "Shipped" (Status ID: 4)
├── Bulk action or individual update
└── OrderController@bulkUpdateStatus()

STEP 2: Froze Stock Converts to Sold (Automatic)
├── For this order:
│   ├── For each OrderItem:
│   │   └── For each OrderItemStock:
│   │       ├── Get Stock record
│   │       ├── stock.froze_quantity: 20 → 0 (remove reservation)
│   │       └── stock.sold_quantity: 0 → 20 (mark as sold)
│   │
│   └── For Stock B:
│       ├── stock.froze_quantity: 10 → 0
│       └── stock.sold_quantity: 0 → 10
│
├── Create VendorAccount record:
│   ├── vendor_id: Vendor of this order
│   ├── order_id: This order ID
│   ├── amount: Total order amount (30000)
│   ├── type: 1 (Debit - vendor owes this)
│   ├── note: "Product order - SSE-13-11-2025-0001-1"
│   └── deposite_by: Current admin
│
└── Order status updated to "Shipped"

RESULT:
├── Stock frozen quantities released to sold
├── Vendor account shows debt (type 1 = debit)
├── Vendor owes the dealership the order amount
└── Order ready for payment collection
```

### WORKFLOW 4: DAMAGE/RETURN PROCESSING

```
STEP 1: Report Damage
├── Admin → Damage/Return/Lost → Create New
├── DamageReturnLostController@create()
│   ├── Select Order
│   ├── System loads order items with stock info
│   └── Display available items
├── Admin selects:
│   ├── Order Item: "Product X - 30 units"
│   ├── Type: "Damage"
│   ├── Quantity: 5 (items damaged)
│   ├── Reason: "Delivery damage - packaging rupture"
│   └── Upload evidence photos

STEP 2: Create Damage Record
├── DamageReturnLostController@store()
│   │
│   ├── Create DamageReturnLost record:
│   │   ├── status: 1 (damage)
│   │   ├── quantity: 5
│   │   ├── reason: [reason]
│   │   └── media: evidence photos
│   │
│   ├── Call processQuantityThroughStocks():
│   │   ├── Distribution among order_item_stocks:
│   │   │   ├── OrderItemStock A: 3 damaged
│   │   │   │   └── damage_quantity: 0 → 3
│   │   │   └── OrderItemStock B: 2 damaged
│   │   │       └── damage_quantity: 0 → 2
│   │   │
│   │   └── Update source stocks:
│   │       ├── Stock A: Already sold (no froze to reduce)
│   │       └── Stock B: Already sold (no froze to reduce)
│   │
│   ├── Call updateOrderItemTotals():
│   │   ├── OrderItem.damage_quantity: 0 → 5
│   │   └── Recalculate effective quantity
│   │
│   ├── Call updateOrderTotals():
│   │   ├── Order.total_damage_quantity: 0 → 5
│   │   └── Recalculate order summary
│   │
│   └── Call updateVendorAccount():
│       └── No credit created for damage (vendor's fault)
│
└── Damage recorded

STEP 3: Process Return
├── If item is returned:
│   │
│   ├── DamageReturnLostController@store():
│   │   ├── Type: Return (status = 2)
│   │   ├── Create DamageReturnLost record
│   │   │
│   │   ├── processQuantityThroughStocks():
│   │   │   └── return_quantity += 3
│   │   │
│   │   ├── updateOrderItemTotals()
│   │   │
│   │   ├── updateOrderTotals()
│   │   │
│   │   └── updateVendorAccount():
│   │       ├── Calculate refund amount: 3 × 1000 = 3000
│   │       ├── Create VendorAccount:
│   │       │   ├── vendor_id: Vendor
│   │       │   ├── order_id: Original order
│   │       │   ├── amount: 3000
│   │       │   ├── type: 2 (Credit - vendor gets refund)
│   │       │   └── note: "Return - Order SSE-..."
│   │       └── Vendor.balance increased by 3000
│   │
│   └── Return processed
│
└── Vendor can now settle less amount (debt - refund)
```

### WORKFLOW 5: VENDOR PAYMENT COLLECTION

```
STEP 1: Collect Payment from Vendor
├── Admin → Payment Collection → Create
├── PaymentCollectionController@create()
│   ├── Search vendors via AJAX
│   ├── Select vendor
│   ├── Load pending orders and balance
│   └── Display form

STEP 2: Record Payment
├── Admin enters:
│   ├── Vendor (selected)
│   ├── Payment Method (Cash, Check, Bank)
│   ├── Amount paid: 10000
│   ├── Collection date
│   └── (Optional) Payment document/receipt

STEP 3: Create Payment Collection Record
├── PaymentCollectionController@store()
│   │
│   ├── Create PaymentCollection record:
│   │   ├── vendor_id: Vendor
│   │   ├── payment_method_id: Method
│   │   ├── amount: 10000
│   │   └── collection_date: Today
│   │
│   └── Create VendorAccount record:
│       ├── vendor_id: Vendor
│       ├── amount: 10000
│       ├── type: 2 (Credit - payment received)
│       ├── note: "Payment collection"
│       └── deposite_by: Current admin
│
├── Transaction recorded
└── Vendor.balance updated (auto-calculated)

STEP 4: Vendor Balance Calculation
├── VendorAccount.getVendorBalance($vendorId):
│   ├── Credits: sum(type=2) = 10000 + 3000 = 13000
│   ├── Debits: sum(type=1) = 30000
│   └── Balance = 13000 - 30000 = -17000
│
├── Negative balance means vendor owes 17000
└── Balance shown in Vendor details page

RESULT:
├── Payment recorded
├── Vendor account updated
├── Balance reflects remaining amount owed
└── Can track payment history per vendor
```

### WORKFLOW 6: INVOICE & REPORTING

```
STEP 1: Generate Invoice
├── Admin → Invoices → Select Order
├── InvoiceController@generateInvoice()
│   ├── Load order with items
│   ├── Load order item stocks (with batch info)
│   ├── Generate PDF using DomPDF
│   └── Include:
│       ├── Order header (invoice ID, dates)
│       ├── Vendor details
│       ├── Order items with:
│       │   ├── Product name
│       │   ├── Quantity, Unit price
│       │   ├── Batch IDs from order_item_stocks
│       │   ├── Discount
│       │   └── Line total
│       ├── Order summary (total, discount, net)
│       ├── Payment status
│       └── Signature lines
│
└── PDF generated and downloaded/printed

STEP 2: Generate Vendor Statement
├── Admin → Vendor → View Details
├── Shows:
│   ├── Vendor balance (calculated)
│   ├── All orders:
│   │   ├── Invoice ID
│   │   ├── Amount
│   │   ├── Status
│   │   └── Date
│   ├── All payments collected
│   ├── Damage/Return records
│   └── Balance history
│
└── Can export to Excel
```

---

## IMPLEMENTATION SEQUENCE FOR NEW FEATURES

When adding new features, follow this sequence:

1. **Database Layer**
   - Create migration
   - Add model with relationships

2. **Controller Layer**
   - Add controller methods
   - Implement business logic
   - Add validations

3. **Routes**
   - Register routes in `Modules/Product/routes/web.php`

4. **Views**
   - Create Blade templates
   - Extend `layouts.app`
   - Add form inputs and display logic

5. **Testing**
   - Test with data
   - Verify all relationships
   - Check calculations

---

## KEY FORMULAS & CALCULATIONS

### Profit Calculation
```
For Single Allocation (OrderItemStock):
  profit_per_unit = sell_price - purchase_price
  actual_profit = profit_per_unit × quantity

For Order Item:
  total_profit = sum of all allocation profits - discount

For Order:
  total_profit = sum of all item profits
```

### Stock Availability
```
available_quantity = quantity - sold - damage - stolen - froze

Status Check:
- If available > 0: Stock available
- If available = 0: Stock exhausted
- If available < 0: System error (should not happen)
```

### Vendor Balance
```
vendor_balance = sum(type=2 credits) - sum(type=1 debits)

Positive balance: Vendor is in credit (dealership owes them)
Negative balance: Vendor owes dealership
Zero balance: Settled
```

---

## IMPORTANT NOTES

1. **Transactions**: All critical operations use `DB::transaction()` to ensure data consistency
2. **Soft Deletes**: Not used - permanent records (can be marked inactive)
3. **Auditing**: Consider adding audit logs for critical operations
4. **Stock Sync**: Always verify froze→sold conversion on order shipment
5. **Cascade**: Deleting order deletes order_items and order_item_stocks
6. **Invoice IDs**: Format "SSE-DD-MM-YY-XXXX-ID" where ID is order ID

---

**Document Version**: 1.0  
**Last Updated**: November 13, 2025  
**Scope**: Product Module - Complete Workflow Documentation
