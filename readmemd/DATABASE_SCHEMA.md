# DATABASE SCHEMA & ER DIAGRAM

## Entity Relationship Diagram (Text Format)

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                        PRODUCT INVENTORY SYSTEM                             │
└──────────────────────────────────────────────────────────────────────────────┘

                              PRODUCTS MODULE
┌────────────────────────────────────────────────────────────────────────────┐

                        ┌─────────────────┐
                        │    PRODUCTS     │
                        ├─────────────────┤
                        │ id (PK)         │
                        │ name            │
                        │ color_id (FK)   │──────────┐
                        │ unit_id (FK)    │──────────┤
                        │ status          │          │
                        │ created_at      │          │
                        └────────┬────────┘          │
                                 │                   │
                    ┌────────────┘                   │
                    │                                │
                    │ has_many                       │
                    ↓                                ↓
        ┌─────────────────────┐          ┌─────────────────┐
        │      STOCKS ⭐      │          │     COLORS      │
        ├─────────────────────┤          ├─────────────────┤
        │ id (PK)             │          │ id (PK)         │
        │ product_id (FK)     │          │ name            │
        │ warehouse_id (FK)───┼──────┐   │ status          │
        │ batch_id            │      │   └─────────────────┘
        │ purchase_price      │      │
        │ quantity            │      │   ┌─────────────────┐
        │ total_price         │      └──→│   WAREHOUSES    │
        │ sell_price          │          ├─────────────────┤
        │ damage_quantity     │          │ id (PK)         │
        │ sold_quantity       │          │ name            │
        │ stolen_quantity     │          │ location        │
        │ transfer_quantity   │          └─────────────────┘
        │ froze_quantity ⭐   │
        │ status              │          ┌─────────────────┐
        │ created_at          │          │     UNITS       │
        └────────┬────────────┘          ├─────────────────┤
                 │                       │ id (PK)         │
                 │ has_many              │ name            │
                 │                       └─────────────────┘
                 ↓
        ┌──────────────────────────┐
        │ ORDER_ITEM_STOCKS ⭐⭐   │ (Smart Allocation Link)
        ├──────────────────────────┤
        │ id (PK)                  │
        │ orderitem_id (FK)────────┼──────────┐
        │ stock_id (FK)────────────┼──────┐   │
        │ quantity                 │      │   │
        │ purchase_price           │      │   │
        │ sell_price               │      │   │
        │ total_price              │      │   │
        │ discount_amount          │      │   │
        │ actual_profit ⭐         │      │   │
        │ return_quantity          │      │   │
        │ damage_quantity          │      │   │
        │ lost_quantity            │      │   │
        │ created_at               │      │   │
        └──────────────────────────┘      │   │
                                          │   │
             ┌────────────────────────────┘   │
             │                                │
             │ belongs_to                     │
             ↓                                │
        ┌──────────────────────┐             │
        │   ORDER_ITEMS ⭐     │             │
        ├──────────────────────┤             │
        │ id (PK)              │             │
        │ order_id (FK)────────┼──────────┐  │
        │ product_id (FK)──────┼──────┐   │  │
        │ quantity             │      │   │  │
        │ purchase_price       │      │   │  │
        │ sell_price           │      │   │  │
        │ total_price          │      │   │  │
        │ discount_price       │      │   │  │
        │ return_quantity      │      │   │  │
        │ damage_quantity      │      │   │  │
        │ lost_quantity        │      │   │  │
        │ created_at           │      │   │  │
        └──────────┬───────────┘      │   │  │
                   │                  │   │  │
                   │ belongs_to       │   │  │
                   ↓                  │   │  │
        ┌──────────────────────┐      │   │  │
        │     ORDERS ⭐⭐⭐     │      │   │  │
        ├──────────────────────┤      │   │  │
        │ id (PK)              │      │   │  │
        │ invoice_id (UNIQUE)  │      │   │  │
        │ admin_id (FK)────────┼──┐   │   │  │
        │ vendor_id (FK)───────┼──┼───┤   │  │
        │ order_status_id (FK) │  │ ┌─┼──┴──┘
        │ total_amount         │  │ │ │
        │ paid_amount          │  │ │ │
        │ total_quantity       │  │ │ │
        │ total_discount_amount│  │ │ │
        │ total_damage_qty     │  │ │ │
        │ total_lost_qty       │  │ │ │
        │ total_return_qty     │  │ │ │
        │ payment_status       │  │ │ │
        │ created_at           │  │ │ │
        └──────────┬───────────┘  │ │ │
                   │              │ │ │
        ┌──────────┘              │ │ │
        │                         │ │ │
        │ has_many                │ │ │
        │                         │ │ │
        └─────────────────────────┘ │ │
                                    │ │
        ┌──────────────────────┐    │ │
        │DAMAGE_RETURN_LOST ⭐ │    │ │
        ├──────────────────────┤    │ │
        │ id (PK)              │    │ │
        │ product_id (FK)──────┼────┤ │
        │ stock_id (FK)───────→│ Stock│
        │ order_id (FK)───────→│ Order│
        │ order_item_id (FK)──→│Order │
        │ order_item_stock_id  │Item  │
        │ quantity             │      │
        │ status (1,2,3)       │      │
        │ purchase_price       │      │
        │ total_price          │      │
        │ reason               │      │
        │ created_at           │      │
        └──────────────────────┘      │
                                      │
             ┌────────────────────────┘
             │
             ↓
        ┌──────────────────────┐
        │     VENDORS          │
        ├──────────────────────┤
        │ id (PK)              │
        │ uuid (UNIQUE)        │
        │ shop_name            │
        │ contact_person       │
        │ email                │
        │ mobile               │
        │ country_id (FK)──────┼──┐
        │ full_address         │  │
        │ lat, long            │  │
        │ status               │  │
        │ created_at           │  │
        └──────────┬───────────┘  │
                   │              │
                   │ has_many     │
                   │              │
                   ↓              │
        ┌──────────────────────────────┐
        │  VENDOR_ACCOUNTS ⭐⭐⭐      │ (Finance Tracking)
        ├──────────────────────────────┤
        │ id (PK)                      │
        │ vendor_id (FK)───────────────┼──┐
        │ order_id (FK)────────────────┘  │
        │ payment_method_id (FK)         │ Vendor
        │ amount                         │
        │ type (1=debit, 2=credit)  ⭐   │
        │ note                           │
        │ collection_date                │
        │ created_by (Admin FK)          │
        │ deposite_by (Admin FK)         │
        │ created_at                     │
        └──────────────────────────────┘
             │
             │ Balance = SUM(type=2) - SUM(type=1)
             ↓
        ┌──────────────────────┐
        │PAYMENT_METHODS       │
        ├──────────────────────┤
        │ id (PK)              │
        │ name (Cash, Check)   │
        │ status               │
        └──────────────────────┘

        ┌──────────────────────┐
        │ORDER_STATUSES        │
        ├──────────────────────┤
        │ id (PK)              │
        │ name                 │
        │ description          │
        │ status               │
        └──────────────────────┘
        
        ┌──────────────────────┐
        │ ADMINS (from app)    │
        ├──────────────────────┤
        │ id (PK)              │
        │ name                 │
        │ email                │
        │ password             │
        └──────────────────────┘

        ┌──────────────────────┐
        │ COUNTRIES (from app) │
        ├──────────────────────┤
        │ id (PK)              │
        │ name                 │
        │ code                 │
        └──────────────────────┘

└────────────────────────────────────────────────────────────────────────────┘

Legend:
⭐   = Critical table
⭐⭐ = Very critical (business logic)
⭐⭐⭐ = Core financial tracking
(FK) = Foreign Key
(PK) = Primary Key
```

## Table Details & Field Reference

### PRODUCTS
```sql
CREATE TABLE products (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    color_id BIGINT UNSIGNED NULLABLE → colors.id,
    measurement_unit_name VARCHAR(255) NULLABLE,
    measurement_unit_number VARCHAR(255) NULLABLE,
    package_unit_name VARCHAR(255) NULLABLE,
    package_unit_quantity VARCHAR(255) NULLABLE,
    unit_id BIGINT UNSIGNED NOT NULL → units.id,
    status BOOLEAN DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

Scopes:
- active() → where status = 1
- Get: product_image_url, product_image_thumb_url
- Relations: color(), unit(), stocks()
```

### STOCKS ⭐ (Inventory Hub)
```sql
CREATE TABLE stocks (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL → products.id,
    warehouse_id BIGINT UNSIGNED NULLABLE → warehouses.id,
    batch_id VARCHAR(255) NOT NULL,
    purchase_price DECIMAL(10, 2) NOT NULL,
    quantity INTEGER NOT NULL,
    total_price DECIMAL(12, 2),
    sell_price DECIMAL(10, 2) NOT NULL,
    damage_quantity INTEGER DEFAULT 0,
    sold_quantity INTEGER DEFAULT 0,
    stolen_quantity INTEGER DEFAULT 0,
    transfer_quantity INTEGER DEFAULT 0,
    froze_quantity INTEGER DEFAULT 0 ⭐,
    status BOOLEAN DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

Key Fields:
- froze_quantity: ⭐ RESERVED for pending orders
- available = quantity - (sold + damage + stolen + transfer + froze)

Scopes:
- active() → where status = 1
- available() → available qty > 0

Attributes:
- remaining_quantity → calculated field

Relations:
- product(), warehouse()
- orderItems(), orderItemStocks()
```

### ORDERS ⭐⭐⭐ (Transaction Hub)
```sql
CREATE TABLE orders (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    invoice_id VARCHAR(255) UNIQUE NOT NULL,
    admin_id BIGINT UNSIGNED NOT NULL → admins.id,
    vendor_id BIGINT UNSIGNED NOT NULL → vendors.id,
    order_status_id BIGINT UNSIGNED NOT NULL → order_statuses.id,
    total_amount DECIMAL(15, 2) DEFAULT 0,
    paid_amount DECIMAL(15, 2) DEFAULT 0,
    total_quantity INTEGER DEFAULT 0,
    total_discount_amount DECIMAL(15, 2) DEFAULT 0,
    total_return_quantity INTEGER DEFAULT 0,
    total_damage_quantity INTEGER DEFAULT 0,
    total_lost_quantity INTEGER DEFAULT 0,
    payment_status INTEGER DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

Key Fields:
- invoice_id: Auto-generated as "SSE-DD-MM-YY-XXXX-ID"
- payment_status: 0=Unpaid, 1=Partial, 2=Paid

Boot:
- Auto-generates invoice_id on create

Scopes:
- active() → status != Cancelled
- cancelled() → status = Cancelled

Methods:
- canBeCancelled() → payment_status=0 AND status not in [shipped, delivered, cancelled]
- cancelOrder() → restore stock, change status
- getTotalProfitAttribute() → calculated from order_item_stocks

Relations:
- admin(), vendor(), orderStatus()
- orderItems() (has_many)
- vendorAccounts() (has_many)
```

### ORDER_ITEMS ⭐⭐ (Item Container)
```sql
CREATE TABLE order_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL → orders.id,
    product_id BIGINT UNSIGNED NOT NULL → products.id,
    quantity INTEGER NOT NULL,
    purchase_price DECIMAL(10, 2),
    sell_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(15, 2),
    discount_price DECIMAL(15, 2) DEFAULT 0,
    return_quantity INTEGER DEFAULT 0,
    damage_quantity INTEGER DEFAULT 0,
    lost_quantity INTEGER DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

Key Fields:
- purchase_price: Average of allocated stock prices
- sell_price: Same for all allocations from this item
- total_price: (sell_price × quantity) - discount

Calculated Attributes:
- getNetPriceAttribute() → (sell × qty) - discount
- getProfitPerItemAttribute() → sell - purchase
- getTotalProfitAttribute() → profit_per_item × qty
- getEffectiveQuantityAttribute() → qty - return - damage - lost
- getProfitMarginAttribute() → (profit / purchase) × 100%
- getStatusAttribute() → Active/Partially Returned/Damaged/Lost

Relations:
- order(), product()
- orderItemStocks() (has_many)
- stock() (belongs_to)
```

### ORDER_ITEM_STOCKS ⭐⭐⭐ (Smart Allocation!)
```sql
CREATE TABLE order_item_stocks (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    orderitem_id BIGINT UNSIGNED NOT NULL → order_items.id,
    stock_id BIGINT UNSIGNED NOT NULL → stocks.id,
    quantity INTEGER NOT NULL,
    purchase_price DECIMAL(10, 2) NOT NULL,
    sell_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2),
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    actual_profit DECIMAL(10, 2) DEFAULT 0 ⭐,
    return_quantity INTEGER DEFAULT 0,
    damage_quantity INTEGER DEFAULT 0,
    lost_quantity INTEGER DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

Purpose: LINKS order items to specific stock batches for:
✓ Batch tracking and traceability
✓ Profit calculation per allocation
✓ Damage/Return/Lost processing per source

Boot:
- auto-calculates actual_profit on save
  = (sell - purchase) × (qty - return - damage - lost)

Calculated Attributes:
- getNetQuantityAttribute() → qty - return - damage - lost
- getProfitPerUnitAttribute() → sell - purchase

Key Relations:
- orderItem(), stock()
```

### VENDORS (Buyer/Supplier)
```sql
CREATE TABLE vendors (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE,
    email VARCHAR(255) NULLABLE,
    mobile VARCHAR(20) NOT NULL,
    shop_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    country_id BIGINT UNSIGNED NULLABLE → countries.id,
    full_address TEXT NULLABLE,
    lat DECIMAL(8, 8) NULLABLE,
    long DECIMAL(8, 8) NULLABLE,
    status BOOLEAN DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

Boot:
- Auto-generates uuid on create

Scopes:
- active() → where status = 1

Methods:
- getBalanceAttribute() → calculated from vendor_accounts

Relations:
- country()
- vendorAccounts() (has_many)
```

### VENDOR_ACCOUNTS ⭐⭐⭐ (Finance Ledger)
```sql
CREATE TABLE vendor_accounts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    vendor_id BIGINT UNSIGNED NOT NULL → vendors.id,
    order_id BIGINT UNSIGNED NULLABLE → orders.id,
    payment_method_id BIGINT UNSIGNED NULLABLE → payment_methods.id,
    amount DECIMAL(15, 2) NOT NULL,
    type INTEGER NOT NULL,
    note TEXT NULLABLE,
    collection_date DATE NULLABLE,
    created_by BIGINT UNSIGNED NULLABLE → admins.id,
    deposite_by BIGINT UNSIGNED NULLABLE → admins.id,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

Key Fields:
- type: 1=Debit (vendor owes us), 2=Credit (we owe vendor)

Transaction Types:
- Type 1 (Debit): Created when order shipped
  → Represents goods given to vendor
  → Increases vendor's debt to dealership
  
- Type 2 (Credit): Created when payment received or return approved
  → Represents payment/refund from vendor
  → Decreases vendor's debt

Methods:
- getTypeTextAttribute() → "Debit" or "Credit"
- scopeDebit() → where type = 1
- scopeCredit() → where type = 2
- static getVendorBalance($vendorId)
  → SUM(type=2) - SUM(type=1)

Relations:
- vendor(), order(), paymentMethod()
- createdBy(), depositeBy() (Admins)
```

### DAMAGE_RETURN_LOST ⭐ (Issue Tracking)
```sql
CREATE TABLE damage_return_losts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL → products.id,
    stock_id BIGINT UNSIGNED NULLABLE → stocks.id,
    order_id BIGINT UNSIGNED NOT NULL → orders.id,
    order_item_id BIGINT UNSIGNED NOT NULL → order_items.id,
    order_item_stock_id BIGINT UNSIGNED NULLABLE → order_item_stocks.id,
    quantity DECIMAL(15, 2) NOT NULL,
    status INTEGER NOT NULL,
    purchase_price DECIMAL(10, 2),
    total_price DECIMAL(10, 2),
    reason TEXT NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

Status Values:
- 1 = Damage
- 2 = Return
- 3 = Lost

Methods:
- getStatusTextAttribute() → damage/return/lost
- scopeDamage() → where status = 1
- scopeLost() → where status = 3

Media:
- evidence_pic collection for damage photos

Relations:
- product(), stock(), order(), orderItem(), orderItemStock()
```

### ORDER_STATUSES
```sql
CREATE TABLE order_statuses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NULLABLE,
    status BOOLEAN DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

Default Statuses:
- 1: Pending
- 2: Processing
- 3: Confirmed ← Order created here
- 4: Shipped ← Froze→Sold conversion happens
- 5: Delivered
- 6: Cancelled
```

## Critical Operations & Their Impact

### Create Order (store method)
```
BEFORE:
  Stock.froze_quantity = 0
  Stock.sold_quantity = 0

AFTER:
  Stock.froze_quantity = allocated_qty ← RESERVED
  Stock.sold_quantity = 0 (unchanged)
  Order.order_status_id = 3 (Confirmed)
  OrderItemStock.* created with batch linking
```

### Update Status to Shipped (bulkUpdateStatus method, Status 4 or 5)
```
BEFORE:
  Stock.froze_quantity = 30
  Stock.sold_quantity = 0
  VendorAccount entries = 0 (for this order)

AFTER:
  Stock.froze_quantity = 0 ← RELEASED
  Stock.sold_quantity = 30 ← CONFIRMED
  VendorAccount created: type=1, amount=order_total
  Order.order_status_id = 4 (Shipped)
```

### Record Damage/Return (store method)
```
BEFORE:
  OrderItemStock.damage_qty = 0
  OrderItemStock.return_qty = 0
  Stock.damage_qty = 0
  Stock.quantity = 100
  VendorAccount entries for return = none

AFTER (Damage):
  OrderItemStock.damage_qty = 5
  Stock.damage_qty += 5 ← WRITTEN OFF
  Stock.quantity = 100 (unchanged)
  VendorAccount: NO CREDIT (vendor's fault)

AFTER (Return):
  OrderItemStock.return_qty = 5
  Stock.quantity = 100 (back in inventory)
  Stock.sold_qty -= 5 (remove from sold)
  VendorAccount created: type=2, amount=refund
```

---

**Document Version**: 1.0  
**Last Updated**: November 13, 2025
