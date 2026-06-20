# Dealership App — Claude Context

## Project Type
Laravel 10+ application using **nwidart/laravel-modules**. All business logic lives in `Modules/`.

## Module Layout
```
Modules/
  Admin/     — admin users, permissions
  Product/   — all business features (vendors, orders, collections, finance)
  Role/      — Spatie roles/permissions
  User/      — end-user management
```

## Product Module Internals
| Layer | Path |
|-------|------|
| Controllers | `Modules/Product/app/Http/Controllers/` |
| Models | `Modules/Product/app/Models/` |
| Migrations | `Modules/Product/database/migrations/` |
| Views | `Modules/Product/resources/views/` |
| Routes | `Modules/Product/routes/web.php` |

## Conventions to Follow Every Time

### Routes (`web.php`)
- All routes live under `Route::prefix('admin')->middleware(['auth:admin'])`.
- Static routes (e.g. `/search-vendors`) **must be declared before** wildcard routes (`{model}`) within the same controller group to avoid mis-routing.
- Route naming pattern: `feature-name.action` e.g. `dsr-collections.index`.

### Controller
- Extend `Illuminate\Routing\Controller` (not `App\Http\Controllers\Controller`).
- Namespace: `Modules\Product\Http\Controllers`.
- Inline `$request->validate()` for simple rules; use a FormRequest class for complex ones.
- Wrap mutations in `DB::transaction()` only when multiple tables are written.

### Model
- Namespace: `Modules\Product\Models`.
- Always define `$fillable`, `$casts`, and `boot()` (to auto-set `created_by` from `Auth::guard('admin')`).
- Media uploads use Spatie Media Library (`InteractsWithMedia`). Only add if needed.
- Relationships: `vendor()` → `Vendor`, `paymentMethod()` → `PaymentMethod`, `createdBy/depositeBy` → `Admin` (app-level model).

### Views
- Extend `layouts.app`.
- Use `@push('custome-css')` / `@push('custome-js')` (note: project spells it "custome", not "custom").
- Delete confirmation: include `@include('components.delete')` and add `delete-btn` class + `data-url` attribute.
- Flash messages: check `session('success')` / `session('error')` at top of card-body.

### Sidebar (`resources/views/layouts/adminsidebar.blade.php`)
- Wrap each menu item in `@can('permission_name') ... @endcan`.
- Active link: `{{ request()->is('admin/route-prefix/*') ? 'active' : '' }}`.
- Icons: MDI (`mdi mdi-*`), e.g. `mdi-cash-multiple`, `mdi-account-cash`.
- Add new items inside the relevant `<ul class="nav nav-treeview">` section.

### Permissions
- Permissions are stored in the DB (`permissions` table) and managed via the admin panel.
- To restrict a page: add `@can('permission_name')` in the sidebar and optionally `$this->authorize('permission_name')` in the controller.
- Never hard-code roles; always use permission names.

### Admin Model
- Core admin user model: `App\Models\Admin`, table `admins`.
- Used for `created_by` and `deposite_by` FK columns in financial tables.
- Fetch active admins: `Admin::where('status', 1)->orderBy('name')->get()`.

---

## Features Reference

### Payment Collection (`vendor_accounts` table)
- Records vendor payments tied to specific orders (invoice allocation).
- Model: `VendorAccount` | Controller: `PaymentCollectionController`.
- Permission: `payment_collection_list`.
- When vendor is selected via AJAX, pending orders are fetched and shown for allocation.

### Vendor Collection / DSR Collection (`dsr_collections` table)
- Records cash/payment collections from a vendor **without linking to any invoice**.
- Purpose: track daily collections by date range and vendor.
- Model: `DsrCollection` | Controller: `DsrCollectionController`.
- Permission: `dsr_collection` (insert via admin panel DB).
- Columns: `vendor_id`, `payment_method_id`, `amount`, `collection_date`, `note`, `deposite_by`, `created_by`.
- Index: filterable by vendor (AJAX name/mobile search), payment method, date range. Shows filtered total.
- Create: AJAX vendor search → no invoice list, just record amount directly.
- AJAX vendor search route: `dsr.vendors.search` (`GET /admin/dsr-collection/search-vendors`).

---

## Key Relationships
```
Vendor  →  many VendorAccount  (payment_collection — tied to orders)
Vendor  →  many DsrCollection  (vendor_collection  — standalone)
Vendor  →  many Order
Order   →  many VendorAccount
PaymentMethod  →  many VendorAccount, DsrCollection
Admin          →  created_by / deposite_by on financial records
```

## Running Migrations
```bash
php artisan module:migrate Product
# or
php artisan migrate
```

## File Naming Reminder
Migration timestamps use `YYYY_MM_DD_HHMMSS_description.php`.

## Common Pitfalls
- Do **not** add invoice/order loading to the DSR Collection create form — it is intentionally invoice-free.
- The project consistently spells "deposite_by" (not "deposited_by") — match this in all new columns.
- The `@push` stack name is `custome-js` and `custome-css` (typo in original project — keep consistent).
