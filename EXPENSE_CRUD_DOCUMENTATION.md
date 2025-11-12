# Expense Management CRUD Implementation

## Overview
Two full CRUD systems have been implemented following the existing Color CRUD pattern:

1. **Expense Head** - Parent entity for expense categories with budget tracking
2. **Expense List** - Child entity for individual expense records

## Database Schema

### Expense Heads Table
- `id` - Primary key
- `title` - Unique expense category name
- `status` - Boolean (active/inactive)
- `max_amount` - Decimal(15,2) - Maximum budget limit
- `created_at`, `updated_at` - Timestamps

### Expense Lists Table
- `id` - Primary key
- `expense_head_id` - Foreign key (cascade on delete)
- `title` - Expense title
- `description` - Text (nullable)
- `amount` - Decimal(15,2) - Expense amount
- `expense_date` - Date
- `reference_no` - String (nullable)
- `status` - Boolean (active/inactive)
- `created_at`, `updated_at` - Timestamps

## Features Implemented

### Expense Head CRUD
**Location:** `Modules/Product/`

#### Model Features (`App/Models/ExpenseHead.php`)
- Relationship: `hasMany` with ExpenseList
- Computed Properties:
  - `total_expenses` - Sum of all related expenses
  - `remaining_amount` - Budget remaining (max_amount - total_expenses)
- Query Scope: `active()` - Filter active expense heads

#### Controller Features (`Http/Controllers/ExpenseHeadController.php`)
- **Validation:**
  - Title: required, unique, max 255 chars
  - Max Amount: required, numeric, >= 0
  - Status: required, boolean
- **Business Logic:**
  - Prevents deletion if expense head has related expense records
  - Custom error messages for all validation rules

#### Views (`resources/views/expense-head/`)
- **index.blade.php:**
  - Table with 9 columns showing:
    - Title, Max Amount, Total Expenses, Remaining Amount (color-coded badge)
    - Expense Count (badge), Status, Created Date, Actions
  - Uses `withCount('expenseLists')` for performance
  - Delete confirmation modal
  
- **create.blade.php:**
  - Form with title, max_amount (number input with 2 decimals), status (select)
  - Helper text for max_amount field
  - Validation error display
  
- **edit.blade.php:**
  - Pre-filled form matching create structure
  - Displays budget information alert if expenses exist
  - Shows current total expenses and remaining amount

### Expense List CRUD
**Location:** `Modules/Product/`

#### Model Features (`App/Models/ExpenseList.php`)
- Relationship: `belongsTo` ExpenseHead
- Date casting for `expense_date` and `amount`

#### Controller Features (`Http/Controllers/ExpenseListController.php`)
- **Validation:**
  - Expense Head: required, must exist
  - Title: required, max 255 chars
  - Amount: required, numeric, > 0
  - Expense Date: required, valid date
  - Reference No: nullable, max 100 chars
  - Status: required, boolean
- **Business Logic:**
  - Validates expense amount against expense head budget
  - Store: Checks if (total_expenses + new_amount) > max_amount
  - Update: Excludes current record from total before checking
  - Returns descriptive error with remaining budget amount
  - Loads only active expense heads in create/edit forms

#### Views (`resources/views/expense-list/`)
- **index.blade.php:**
  - Table with 9 columns showing:
    - Expense Head (linked), Title, Description (truncated), Amount
    - Expense Date, Reference No, Status, Actions
  - Footer row showing total of all expenses
  - DataTables integration (sorted by expense_date desc)
  - Delete confirmation modals
  
- **create.blade.php:**
  - Form with all expense fields
  - Expense Head dropdown showing max_amount and remaining budget
  - JavaScript features:
    - Real-time budget info display on expense head selection
    - Amount validation warning if exceeds remaining budget
    - Visual feedback (red text) when budget exceeded
  - Date input defaults to today
  
- **edit.blade.php:**
  - Pre-filled form matching create structure
  - JavaScript adjusts remaining budget calculation to include current expense
  - Shows available budget including the amount being edited

## Routes
**File:** `Modules/Product/routes/web.php`

### Expense Head Routes (admin prefix)
- `GET /admin/expense-head/index` → index (admin.expenseHeadIndex)
- `GET /admin/expense-head/create` → create (admin.expenseHeadCreate)
- `POST /admin/expense-head/store` → store (admin.expenseHeadStore)
- `GET /admin/expense-head/{expenseHead}/edit` → edit (admin.expenseHeadEdit)
- `PUT /admin/expense-head/{expenseHead}/update` → update (admin.expenseHeadUpdate)
- `DELETE /admin/expense-head/{expenseHead}/delete` → destroy (admin.expenseHeadDestroy)

### Expense List Routes (admin prefix)
- `GET /admin/expense-list/index` → index (admin.expenseListIndex)
- `GET /admin/expense-list/create` → create (admin.expenseListCreate)
- `POST /admin/expense-list/store` → store (admin.expenseListStore)
- `GET /admin/expense-list/{expenseList}/edit` → edit (admin.expenseListEdit)
- `PUT /admin/expense-list/{expenseList}/update` → update (admin.expenseListUpdate)
- `DELETE /admin/expense-list/{expenseList}/delete` → destroy (admin.expenseListDestroy)

## Admin Sidebar
**File:** `resources/views/layouts/adminsidebar.blade.php`

Added to **Financial** section:
- Expense Heads (icon: mdi-format-list-text)
- Expense Lists (icon: mdi-currency-usd)

Menu active states configured for:
- `admin/expense-head/*`
- `admin/expense-list/*`

## Business Logic Rules

1. **Budget Enforcement:**
   - Each expense head has a max_amount budget limit
   - Total of all expenses in a head cannot exceed max_amount
   - Create/update operations validate against remaining budget
   - Error message shows how much budget is remaining

2. **Data Integrity:**
   - Expense heads with related expenses cannot be deleted
   - Foreign key cascade ensures orphaned records are prevented
   - Only active expense heads shown in expense list forms

3. **Financial Tracking:**
   - Real-time calculation of total expenses per head
   - Remaining budget displayed with color coding (red if negative)
   - Expense count badges for quick overview
   - Total expenses shown in footer of expense list table

## UI/UX Features

1. **Visual Feedback:**
   - Color-coded remaining amount badges (red when over budget)
   - Status badges (green for active, red for inactive)
   - Expense count badges on expense head index
   - Truncated descriptions with hover tooltip

2. **JavaScript Enhancements:**
   - Real-time budget validation in create/edit forms
   - Dynamic budget info display on expense head selection
   - Warning messages when amount exceeds budget
   - DataTables for sorting, pagination, and search

3. **User Experience:**
   - Breadcrumb navigation on all pages
   - Confirmation modals before deletion
   - Success/error alert messages
   - Clear action buttons (Edit/Delete)
   - Helper text on important fields

## Database Migrations
Both migrations executed successfully:
- ✅ `2025_11_11_200358_create_expense_heads_table`
- ✅ `2025_11_11_200430_create_expense_lists_table`

## File Structure
```
Modules/Product/
├── Http/Controllers/
│   ├── ExpenseHeadController.php (Full CRUD with budget logic)
│   └── ExpenseListController.php (Full CRUD with validation)
├── resources/views/
│   ├── expense-head/
│   │   ├── index.blade.php (Table with financial tracking)
│   │   ├── create.blade.php (Budget input form)
│   │   └── edit.blade.php (Update with expense info)
│   └── expense-list/
│       ├── index.blade.php (DataTable with totals)
│       ├── create.blade.php (Form with budget warnings)
│       └── edit.blade.php (Update with budget recalc)
└── routes/web.php (12 new routes added)

App/Models/
├── ExpenseHead.php (With computed properties and relationships)
└── ExpenseList.php (BelongsTo relationship)

database/migrations/
├── 2025_11_11_200358_create_expense_heads_table.php
└── 2025_11_11_200430_create_expense_lists_table.php
```

## Testing Checklist
- [ ] Create expense head with budget
- [ ] Verify computed properties (total_expenses, remaining_amount)
- [ ] Create expense within budget limit
- [ ] Test budget validation (create expense exceeding limit)
- [ ] Update expense and verify budget recalculation
- [ ] Try deleting expense head with expenses (should fail)
- [ ] Delete expense list and verify cascade
- [ ] Check DataTables sorting/filtering on expense list index
- [ ] Verify status badges and color coding
- [ ] Test navigation through breadcrumbs and sidebar
