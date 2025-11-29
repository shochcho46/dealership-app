# GETTING STARTED - QUICK START GUIDE

## 📖 5-MINUTE PROJECT OVERVIEW

### What is this app?
A dealership inventory and order management system. It handles products, stocks, orders, and vendor payments.

### Core Workflow (in order):
1. **Create Products** → Give products names, colors, units
2. **Add Stock** → Add inventory with batch IDs and pricing
3. **Create Orders** → Select vendor, add items → System allocates from best stocks
4. **Ship Orders** → Change status → Stock converts from frozen to sold
5. **Track Issues** → Report damage/returns → System updates stock & refunds
6. **Collect Payment** → Record vendor payments → Auto-calculate balance

---

## 🗂️ FIND YOUR FILES QUICKLY

| Task | Go To |
|------|-------|
| Understand everything | `PROJECT_ARCHITECTURE_GUIDE.md` |
| Daily coding reference | `QUICK_REFERENCE.md` |
| Database questions | `DATABASE_SCHEMA.md` |
| Navigate docs | `DOCUMENTATION_INDEX.md` |
| Big picture summary | `PROJECT_SUMMARY.md` |

---

## 🧭 QUICK NAVIGATION MAP

```
┌──────────────────────────────────────────────────────────┐
│          I NEED TO UNDERSTAND / FIX                      │
└──────────────────────────────────────────────────────────┘

How the app works?
  └──> PROJECT_ARCHITECTURE_GUIDE → "Project Overview"

How orders are created?
  └──> PROJECT_ARCHITECTURE_GUIDE → "Workflow 2: Order Creation"

What are smart allocations?
  └──> PROJECT_ARCHITECTURE_GUIDE → "Stock Management System"

How does damage/return work?
  └──> PROJECT_ARCHITECTURE_GUIDE → "Workflow 4: Damage/Return"

What tables exist?
  └──> QUICK_REFERENCE → "Database Tables Cheat Sheet"

Database structure & relationships?
  └──> DATABASE_SCHEMA → "Entity Relationship Diagram"

Which controller method does X?
  └──> QUICK_REFERENCE → "Controller Method Reference"

Which route is for X?
  └──> QUICK_REFERENCE → "Routes Quick Reference"

Order won't create / stuck?
  └──> QUICK_REFERENCE → "Troubleshooting Checklist"

How to calculate vendor balance?
  └──> QUICK_REFERENCE → "Key Formulas & Calculations"

How views work?
  └──> PROJECT_ARCHITECTURE_GUIDE → "View Hierarchy & Template Extension"

Adding a new feature?
  └──> PROJECT_ARCHITECTURE_GUIDE → "Implementation Sequence"

What AJAX endpoints exist?
  └──> QUICK_REFERENCE → "API Endpoints (AJAX)"
```

---

## 🎯 KEY CONCEPTS IN 30 SECONDS

### 1. Smart Stock Allocation
When creating an order:
- System finds all available stocks
- Picks highest-price stocks first (maximize profit)
- Creates links (order_item_stocks) to those batches
- RESERVES those stocks (frozen_quantity++)

### 2. Frozen vs Sold
- **Frozen**: Reserved for pending orders (unavailable for others)
- **Sold**: Confirmed shipped/delivered (belongs to vendor)
- When order ships: frozen → sold (automatic)

### 3. Vendor Ledger (Financial Tracking)
- **Type 1 (Debit)**: When order given to vendor = they owe us
- **Type 2 (Credit)**: When payment received = debt reduced
- **Balance = Credits - Debits**
- Negative balance = vendor owes us money

### 4. Damage/Return/Lost
- **Damage** (vendor's fault) = No refund
- **Return** (valid) = Vendor gets CREDIT (refund)
- **Lost** (missing) = No refund
- All reduce available quantity

---

## 📊 QUICK STATS

| Item | Value |
|------|-------|
| Main Module | Product |
| Key Tables | 10+ |
| Controllers | 10+ |
| Models | 15+ |
| Routes (admin) | 30+ |
| Key Features | 6 |
| Documentation Files | 5 |
| Total Docs Pages | 100+ |

---

## 🚀 COMMON TASKS

### Task 1: Understand Order Creation
**File**: `PROJECT_ARCHITECTURE_GUIDE.md`
**Section**: "Workflow 2: Order Creation with Smart Allocation"
**Time**: 10 minutes

### Task 2: Find a Route
**File**: `QUICK_REFERENCE.md`
**Section**: "Routes Quick Reference"
**Time**: 2 minutes

### Task 3: Add New Controller Method
**File**: `PROJECT_ARCHITECTURE_GUIDE.md`
**Section**: "Implementation Sequence for New Features"
**Time**: 15 minutes

### Task 4: Fix Order Calculation
**File**: `QUICK_REFERENCE.md`
**Section**: "Troubleshooting Checklist"
**Time**: 5 minutes

### Task 5: Understand Database
**File**: `DATABASE_SCHEMA.md`
**Section**: "Entity Relationship Diagram"
**Time**: 15 minutes

---

## 💻 KEY CODE LOCATIONS

### Controllers (Business Logic)
```
Modules/Product/app/Http/Controllers/
├── OrderController.php ⭐
│   ├── store() → Create order with smart allocation
│   ├── allocateStockForOrderItem() → Smart allocation algorithm
│   ├── bulkUpdateStatus() → Update status & convert froze→sold
│   └── cancel() → Cancel order & restore stock
│
├── DamageReturnLostController.php ⭐
│   ├── store() → Record damage/return/lost
│   └── processQuantityThroughStocks() → Distribute among batches
│
├── VendorController.php
├── StockController.php
├── ProductController.php
└── [Other Controllers]
```

### Models (Data Structure)
```
Modules/Product/app/Models/
├── Order.php ⭐
├── Stock.php ⭐
├── OrderItem.php ⭐
├── OrderItemStock.php ⭐ (Smart allocation link)
├── VendorAccount.php ⭐ (Financial tracking)
├── DamageReturnLost.php ⭐
├── Vendor.php
├── Product.php
└── [Other Models]
```

### Views (UI)
```
Modules/Product/resources/views/
├── order/
│   ├── index.blade.php → Order list
│   ├── create.blade.php → Create form
│   ├── edit.blade.php → Edit form
│   └── show.blade.php → Details
├── stock/
├── product/
├── vendor/
├── damage-return-lost/
└── [Other views]

Base Layout:
resources/views/layouts/
└── app.blade.php ← Master template
```

### Routes
```
Modules/Product/routes/web.php → All product routes
routes/admin.php → Admin auth routes
```

---

## 🔍 HOW TO FIND THINGS

### Need to find a controller method?
```
Search QUICK_REFERENCE.md for method name
or
Go to Modules/Product/app/Http/Controllers/
```

### Need to understand a table?
```
Search DATABASE_SCHEMA.md for table name
Look for: Field names, relationships, calculations
```

### Need a route?
```
Search QUICK_REFERENCE.md "Routes Quick Reference"
or
Search Modules/Product/routes/web.php
```

### Need to understand a model relationship?
```
Search DATABASE_SCHEMA.md "Entity Relationship Diagram"
or
Open the model file and read the relation methods
```

### Need to understand business logic?
```
Search PROJECT_ARCHITECTURE_GUIDE.md for workflow
or
Look in the controller method (read comments)
```

---

## 📝 COMMON CODE PATTERNS

### Create a Resource (Product, Vendor, etc.)
```php
// Controller
public function store(Request $request)
{
    $request->validate([...]); // Validate
    
    $resource = Model::create($request->validated()); // Create
    
    return redirect()->route('admin.resourceIndex')
        ->with('success', 'Created successfully');
}
```

### Get Data with Relationships
```php
// Get order with everything
$order = Order::with([
    'vendor',
    'orderStatus',
    'orderItems.product',
    'orderItems.orderItemStocks.stock'
])->find($id);

// Available stock calculation
$available = Stock::whereRaw(
    'quantity > (sold_quantity + damage_quantity + stolen_quantity + froze_quantity)'
)->get();
```

### Handle Transaction
```php
DB::beginTransaction();
try {
    // Do stuff
    DB::commit();
    return redirect()->with('success', 'Done!');
} catch (\Exception $e) {
    DB::rollback();
    return redirect()->back()->with('error', $e->getMessage());
}
```

### Create View
```blade
@extends('layouts.app')

@push('custome-css')
    <style>/* CSS */</style>
@endpush

@section('content')
    <!-- HTML -->
@endsection

@push('custome-js')
    <script>/* JS */</script>
@endpush
```

---

## ⚠️ IMPORTANT THINGS TO REMEMBER

✅ **DO:**
- Use transactions for critical operations
- Eager load relationships with `with()`
- Validate all user input
- Use scopes for query filters
- Calculate available stock correctly
- Test your changes with data

❌ **DON'T:**
- Modify froze_quantity without reason
- Create orders without stock allocation
- Update status without checking canBeCancelled()
- Calculate profit from order_items instead of order_item_stocks
- Forget to create vendor account when shipping
- Assume quantities are correct (calculate from components)

---

## 🐛 QUICK FIXES

### Order Not Creating?
1. Check vendor exists and is active
2. Check stock availability (must be > 0)
3. Check items array is not empty
4. Check all validations pass

### Wrong Profit?
1. Check purchase_price in order_item_stocks (not order_items)
2. Verify sell_price is set correctly
3. Check actual_profit calculation in boot method

### Vendor Balance Wrong?
1. Query VendorAccount table for vendor_id
2. Sum Type 1 (Debits) = what vendor owes
3. Sum Type 2 (Credits) = what vendor paid
4. Balance = Credits - Debits

### Can't Cancel Order?
1. Check payment_status == 0 (must be unpaid)
2. Check order status != shipped/delivered/cancelled
3. Call canBeCancelled() to verify

### Damage Quantity Wrong?
1. Check it distributed across order_item_stocks
2. Verify quantities don't exceed ordered qty
3. Check stock quantities updated correctly

---

## 🎓 LEARNING EXERCISES

### Exercise 1: Trace an Order Creation (30 min)
1. Read PROJECT_ARCHITECTURE_GUIDE → Workflow 2
2. Open OrderController@store()
3. Read allocateStockForOrderItem() method
4. Open Database_SCHEMA → order_item_stocks
5. Understand the complete flow

### Exercise 2: Understand Vendor Balance (20 min)
1. Read QUICK_REFERENCE → Key Formulas
2. Open VendorAccount model
3. Read getVendorBalance() method
4. Look at vendor_accounts table
5. Calculate an example manually

### Exercise 3: Follow a Damage Report (25 min)
1. Read PROJECT_ARCHITECTURE_GUIDE → Workflow 4
2. Open DamageReturnLostController@store()
3. Read processQuantityThroughStocks() method
4. See how order_item_stocks are updated
5. Trace vendor account creation

### Exercise 4: Create Simple Report (45 min)
1. Create a new controller method
2. Query orders with relationships
3. Calculate totals
4. Return view with data
5. Create blade template to display

---

## 📞 GETTING HELP

When stuck:
1. **Check QUICK_REFERENCE** first (fastest)
2. **Search in PROJECT_ARCHITECTURE_GUIDE** (comprehensive)
3. **Look at DATABASE_SCHEMA** (for data issues)
4. **Check similar controller/view** (copy patterns)
5. **Read code comments** (in the source files)

---

## ✅ YOU ARE READY IF YOU UNDERSTAND:

- [ ] Modular architecture (Product module)
- [ ] Smart stock allocation concept
- [ ] Frozen vs Sold stock difference
- [ ] Vendor ledger (Type 1 & 2)
- [ ] Order lifecycle (pending → shipped → delivered)
- [ ] Damage/return/lost handling
- [ ] View extends and template inheritance
- [ ] Basic Laravel (Models, Controllers, Routes)

---

## 🎯 YOUR NEXT STEPS

### Immediate (Today):
1. Read **PROJECT_SUMMARY.md** (10 min overview)
2. Read **QUICK_REFERENCE.md** first section (5 min)
3. Open the code in IDE and explore

### Short Term (This Week):
1. Read **PROJECT_ARCHITECTURE_GUIDE.md** completely
2. Read **DATABASE_SCHEMA.md** completely
3. Trace through an order creation manually
4. Run the app and create a test order

### Long Term (This Month):
1. Make a small code change
2. Add a new feature (follow implementation checklist)
3. Write tests for your code
4. Document any improvements you find

---

## 💡 TIPS FOR SUCCESS

1. **Start with the docs** - They're comprehensive and accurate
2. **Search before asking** - Answer is likely in docs
3. **Follow existing patterns** - Code follows conventions
4. **Test your changes** - Run with real data
5. **Keep notes** - Write down your learnings
6. **Ask questions** - If docs don't explain something clearly
7. **Read the code** - Comments explain the "why"
8. **Look at tests** - Show expected behavior

---

## 📚 DOCUMENTATION HIERARCHY

**By Complexity:**
1. PROJECT_SUMMARY.md (simplest, overview)
2. QUICK_REFERENCE.md (quick lookups)
3. PROJECT_ARCHITECTURE_GUIDE.md (comprehensive)
4. DATABASE_SCHEMA.md (detailed structure)
5. Source code (most detailed)

**By Use Case:**
- First time? → PROJECT_SUMMARY.md + QUICK_REFERENCE.md
- Implementing feature? → PROJECT_ARCHITECTURE_GUIDE.md
- Database questions? → DATABASE_SCHEMA.md
- Lost/confused? → DOCUMENTATION_INDEX.md

---

## 🎊 CONGRATULATIONS!

You have access to **5 comprehensive documentation files** that cover:
- ✅ Project architecture
- ✅ Complete workflows
- ✅ Database structure
- ✅ Code reference
- ✅ Quick lookups
- ✅ Troubleshooting
- ✅ Implementation guide

**You have everything you need to master this project!**

---

**Start with:** `PROJECT_SUMMARY.md` (quick read)  
**Then move to:** `PROJECT_ARCHITECTURE_GUIDE.md` (deep dive)  
**Reference:** `QUICK_REFERENCE.md` (daily coding)  

**Happy coding! 🚀**
