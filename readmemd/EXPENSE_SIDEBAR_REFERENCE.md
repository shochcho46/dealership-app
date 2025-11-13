# Expense Management - Sidebar Menu Reference

## ✅ Status: Sidebar Menu Fully Implemented

The sidebar menu items for Expense Management are **already complete and active** in your application.

---

## 📍 Menu Location

### Path in Sidebar
```
Dashboard
│
├── INVENTORY MANAGEMENT
│   ├── Products
│   ├── Stock Management
│   └── Vendors
│
├── Orders
│
└── Financial ⭐
    ├── Payment Collection
    ├── Invoices
    ├── Payment Methods
    ├── Expense Heads ⭐
    └── Expense Lists ⭐
```

---

## 🎯 Menu Items Details

### 1. Expense Heads Menu Item
**File Location**: `resources/views/layouts/adminsidebar.blade.php` (Line 166-171)

```blade
<li class="nav-item">
    <a href="{{ route('admin.expenseHeadIndex') }}" class="nav-link {{ request()->is('admin/expense-head/*') ? 'active' : '' }}">
        <i class="nav-icon mdi mdi-format-list-text"></i>
        <p>Expense Heads</p>
    </a>
</li>
```

**Details:**
- **Icon**: `mdi mdi-format-list-text` (Text List Icon)
- **Label**: "Expense Heads"
- **Route**: `admin.expenseHeadIndex`
- **Active Class**: Applies when URL is `/admin/expense-head/*`
- **Destination**: Shows list of all expense categories with budget limits

### 2. Expense Lists Menu Item
**File Location**: `resources/views/layouts/adminsidebar.blade.php` (Line 172-177)

```blade
<li class="nav-item">
    <a href="{{ route('admin.expenseListIndex') }}" class="nav-link {{ request()->is('admin/expense-list/*') ? 'active' : '' }}">
        <i class="nav-icon mdi mdi-currency-usd"></i>
        <p>Expense Lists</p>
    </a>
</li>
```

**Details:**
- **Icon**: `mdi mdi-currency-usd` (Dollar Sign Icon)
- **Label**: "Expense Lists"
- **Route**: `admin.expenseListIndex`
- **Active Class**: Applies when URL is `/admin/expense-list/*`
- **Destination**: Shows list of all individual expenses

---

## 🔗 Routes Mapped

### Expense Head Routes
| HTTP Method | Route | Name | Controller Method | Purpose |
|------------|-------|------|-------------------|---------|
| GET | `/admin/expense-head` | `admin.expenseHeadIndex` | `index()` | List all expense heads |
| GET | `/admin/expense-head/create` | `admin.expenseHeadCreate` | `create()` | Show create form |
| POST | `/admin/expense-head` | `admin.expenseHeadStore` | `store()` | Save new expense head |
| GET | `/admin/expense-head/{id}/edit` | `admin.expenseHeadEdit` | `edit()` | Show edit form |
| PUT | `/admin/expense-head/{id}` | `admin.expenseHeadUpdate` | `update()` | Save changes |
| DELETE | `/admin/expense-head/{id}` | `admin.expenseHeadDestroy` | `destroy()` | Delete expense head |

### Expense List Routes
| HTTP Method | Route | Name | Controller Method | Purpose |
|------------|-------|------|-------------------|---------|
| GET | `/admin/expense-list` | `admin.expenseListIndex` | `index()` | List all expenses |
| GET | `/admin/expense-list/create` | `admin.expenseListCreate` | `create()` | Show create form |
| POST | `/admin/expense-list` | `admin.expenseListStore` | `store()` | Save new expense |
| GET | `/admin/expense-list/{id}/edit` | `admin.expenseListEdit` | `edit()` | Show edit form |
| PUT | `/admin/expense-list/{id}` | `admin.expenseListUpdate` | `update()` | Save changes |
| DELETE | `/admin/expense-list/{id}` | `admin.expenseListDestroy` | `destroy()` | Delete expense |

---

## 🏗️ Parent Container (Financial Section)

The menu items are nested inside the **Financial** section dropdown:

**File Location**: `resources/views/layouts/adminsidebar.blade.php` (Line 137-178)

```blade
<!-- Financial Section -->
<li class="nav-item {{ request()->is('admin/payment-collection/*') || request()->is('admin/invoice/*') || request()->is('admin/payment-method/*') || request()->is('admin/expense-head/*') || request()->is('admin/expense-list/*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->is('admin/payment-collection/*') || request()->is('admin/invoice/*') || request()->is('admin/payment-method/*') || request()->is('admin/expense-head/*') || request()->is('admin/expense-list/*') ? 'active' : '' }}">
        <i class="nav-icon mdi mdi-finance"></i>
        <p>
            Financial
            <i class="nav-arrow bi bi-chevron-right"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <!-- Payment Collection -->
        <!-- Invoices -->
        <!-- Payment Methods -->
        <!-- Expense Heads ⭐ -->
        <!-- Expense Lists ⭐ -->
    </ul>
</li>
```

**Key Features:**
- **Icon**: `mdi mdi-finance` (Finance/Money icon)
- **Dropdown Arrow**: `bi bi-chevron-right` (Chevron icon)
- **Auto-Expand Logic**: Menu expands when any child routes are active
- **Active Detection**: Checks multiple route patterns with `||` (OR operator)

---

## 🎨 CSS Classes & Styling

### Menu Item Classes
```blade
<li class="nav-item">              <!-- Container for menu item -->
    <a href="..." 
       class="nav-link             <!-- Link styling -->
              {{ request()->is('admin/expense-head/*') ? 'active' : '' }}  <!-- Active state -->
       ">
        <i class="nav-icon mdi mdi-format-list-text"></i>  <!-- Icon styling -->
        <p>Expense Heads</p>        <!-- Text label -->
    </a>
</li>
```

### Classes Explanation
| Class | Purpose |
|-------|---------|
| `nav-item` | Container styling for list item |
| `nav-link` | Link styling (color, padding, hover) |
| `active` | Applied when current URL matches pattern |
| `nav-icon` | Icon wrapper styling |
| `mdi mdi-*` | Material Design Icons (Font Awesome alternative) |

---

## 🔍 Active State Detection

Both menu items use `request()->is()` helper to determine when to apply the **active** class:

```blade
{{ request()->is('admin/expense-head/*') ? 'active' : '' }}
```

**How it works:**
- Checks if current URL matches pattern `/admin/expense-head/*`
- If match: Adds `active` class (highlights menu item)
- If no match: No class added

**Example scenarios:**
| URL | Expense Heads Active? | Expense Lists Active? |
|-----|--------------------|--------------------|
| `/admin/expense-head` | ✅ Yes | ❌ No |
| `/admin/expense-head/create` | ✅ Yes | ❌ No |
| `/admin/expense-head/5/edit` | ✅ Yes | ❌ No |
| `/admin/expense-list` | ❌ No | ✅ Yes |
| `/admin/expense-list/create` | ❌ No | ✅ Yes |
| `/admin/expense-list/2/edit` | ❌ No | ✅ Yes |
| `/admin/dashboard` | ❌ No | ❌ No |

---

## 📝 Controller & Model Association

### ExpenseHeadController
**File**: `Modules/Product/app/Http/Controllers/ExpenseHeadController.php`

**Methods:**
```php
public function index()              // Displays list (route: admin.expenseHeadIndex)
public function create()             // Shows create form (route: admin.expenseHeadCreate)
public function store(Request)       // Saves new record (route: admin.expenseHeadStore)
public function edit(ExpenseHead)    // Shows edit form (route: admin.expenseHeadEdit)
public function update(Request)      // Saves changes (route: admin.expenseHeadUpdate)
public function destroy(ExpenseHead) // Deletes record (route: admin.expenseHeadDestroy)
```

**Model**: `Modules/Product/app/Models/ExpenseHead.php`
```php
class ExpenseHead extends Model
{
    protected $fillable = ['title', 'max_amount', 'status'];
    
    public function expenseLists()
    {
        return $this->hasMany(ExpenseList::class);
    }
}
```

### ExpenseListController
**File**: `Modules/Product/app/Http/Controllers/ExpenseListController.php`

**Methods:**
```php
public function index()              // Displays list (route: admin.expenseListIndex)
public function create()             // Shows create form (route: admin.expenseListCreate)
public function store(Request)       // Saves new record (route: admin.expenseListStore)
public function edit(ExpenseList)    // Shows edit form (route: admin.expenseListEdit)
public function update(Request)      // Saves changes (route: admin.expenseListUpdate)
public function destroy(ExpenseList) // Deletes record (route: admin.expenseListDestroy)
```

**Model**: `Modules/Product/app/Models/ExpenseList.php`
```php
class ExpenseList extends Model
{
    protected $fillable = [
        'expense_head_id', 'title', 'description',
        'amount', 'expense_date', 'reference_no', 'status'
    ];
    
    public function expenseHead()
    {
        return $this->belongsTo(ExpenseHead::class);
    }
}
```

---

## 🚀 How to Use the Menu

### Accessing Expense Heads
1. **Click** on the **Financial** dropdown in sidebar
2. **Click** on **Expense Heads**
3. **See** list of all expense categories with:
   - Category name
   - Maximum budget limit
   - Number of associated expenses
   - Status (Active/Inactive)
   - Action buttons (Edit, Delete)

### Accessing Expense Lists
1. **Click** on the **Financial** dropdown in sidebar
2. **Click** on **Expense Lists**
3. **See** list of all individual expenses with:
   - Expense title
   - Expense head category
   - Amount
   - Date
   - Status
   - Action buttons (Edit, Delete)

### Creating New Items

#### Create Expense Head
1. Go to **Financial → Expense Heads**
2. Click **"Create Expense Head"** button
3. Fill in:
   - **Title**: Category name (e.g., "Office Supplies")
   - **Max Amount**: Budget limit (e.g., 50,000)
   - **Status**: Toggle active/inactive
4. Click **Save**

#### Create Expense
1. Go to **Financial → Expense Lists**
2. Click **"Create Expense"** button
3. Fill in:
   - **Expense Head**: Select category (dropdown)
   - **Title**: Expense description (e.g., "Stationery purchase")
   - **Description**: Optional notes
   - **Amount**: Expense amount
   - **Expense Date**: Date of expense
   - **Reference No**: Optional invoice number
   - **Status**: Toggle active/inactive
4. Click **Save**
   - System checks if amount exceeds category budget
   - Shows error if limit would be exceeded

---

## 🔐 Validation & Constraints

### Expense Head Validations
| Field | Rules | Behavior |
|-------|-------|----------|
| title | required, unique, max 255 | Cannot create duplicate categories |
| max_amount | required, numeric, min 0 | Must be positive number |
| status | required, boolean | Must be true/false |

**Delete Constraint**: Cannot delete expense head if it has associated expenses

### Expense List Validations
| Field | Rules | Behavior |
|-------|-------|----------|
| expense_head_id | required, exists | Must select valid category |
| title | required, string, max 255 | Cannot be empty |
| amount | required, numeric, min 0 | Must be positive number |
| expense_date | required, date | Must be valid date |
| status | required, boolean | Must be true/false |

**Budget Check**: Expense amount cannot exceed category's remaining budget

---

## 💡 Design Pattern

The Expense Management system follows the **Master-Detail pattern**:

```
ExpenseHead (Master)
    └── Can have many ExpenseLists (Details)
        
Financial Section Menu
    ├── Expense Heads (Master List)
    │   ├── Create new category
    │   ├── Edit category
    │   └── Delete category
    │
    └── Expense Lists (Detail List)
        ├── Create new expense (tied to a category)
        ├── Edit expense
        └── Delete expense
```

---

## 📊 Database Relationships

```
expense_heads table
├── id (PK)
├── title (UNIQUE)
├── max_amount (DECIMAL)
├── status (BOOLEAN)
└── timestamps

    ↓ (One-to-Many)
    
expense_lists table
├── id (PK)
├── expense_head_id (FK)
├── title
├── description
├── amount (DECIMAL)
├── expense_date (DATE)
├── reference_no
├── status (BOOLEAN)
└── timestamps
```

---

## ✨ Features Summary

| Feature | Expense Heads | Expense Lists |
|---------|---------------|---------------|
| **List View** | ✅ View all categories | ✅ View all expenses |
| **Create** | ✅ Add new category | ✅ Add new expense |
| **Edit** | ✅ Modify category | ✅ Modify expense |
| **Delete** | ✅ Remove category (if no expenses) | ✅ Remove expense |
| **Budget Tracking** | ✅ Set max budget | ✅ Enforce budget limits |
| **Status Toggle** | ✅ Active/Inactive | ✅ Active/Inactive |
| **Relationships** | N/A | ✅ Links to categories |
| **Cascade Delete** | N/A | ✅ Deletes with parent |

---

## 🎯 Common Tasks

### Check Budget Status
1. Go to **Financial → Expense Heads**
2. View `remaining_amount` calculated as: `max_amount - SUM(expenses.amount)`
3. Shows how much budget is left

### View Expenses for Category
1. Go to **Financial → Expense Heads**
2. Click on category (or find in Expense Lists filtered by head)
3. See all expenses linked to that category
4. Check total against budget limit

### Track Expense References
1. Go to **Financial → Expense Lists**
2. View **Reference No** column
3. Links to invoices, bills, receipts for audit trail

### Export Expense Report
*(If enabled in views)*
1. Go to **Financial → Expense Lists**
2. Optionally click **Export** button
3. Get list in Excel/PDF format

---

## 📌 File References

| Purpose | File Path |
|---------|-----------|
| Sidebar Menu | `resources/views/layouts/adminsidebar.blade.php` |
| Controller | `Modules/Product/app/Http/Controllers/ExpenseHeadController.php` |
| Controller | `Modules/Product/app/Http/Controllers/ExpenseListController.php` |
| Model | `Modules/Product/app/Models/ExpenseHead.php` |
| Model | `Modules/Product/app/Models/ExpenseList.php` |
| Views (Head) | `Modules/Product/resources/views/expense-head/` |
| Views (List) | `Modules/Product/resources/views/expense-list/` |
| Routes | `Modules/Product/routes/web.php` |

---

## ✅ Implementation Checklist

- [x] Sidebar menu items added
- [x] Controllers created (both)
- [x] Models created (both)
- [x] Views created (all CRUD)
- [x] Routes defined (all)
- [x] Validations implemented
- [x] Budget constraint enforced
- [x] Delete protection added
- [x] Error handling implemented
- [x] Success messages added
- [x] Active state detection working

---

**Status**: All Expense Management features are **LIVE and READY TO USE** ✅

Generated: November 13, 2025
