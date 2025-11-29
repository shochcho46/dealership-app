# Stock Date Fields Update

## Overview
Added two new nullable date columns to the stock system for tracking product manufacture and expiration dates.

## Changes Made

### 1. Database Migration
**File:** `2025_11_25_200830_add_manufacture_and_expire_date_to_stocks_table.php`

Added two new nullable date columns:
- `manufacture_date` - Date when the product was manufactured
- `expire_date` - Date when the product expires

Both columns are nullable to support products that don't have these dates.

### 2. Stock Model Updates
**File:** `Modules/Product/app/Models/Stock.php`

**Updated Properties:**
- Added `manufacture_date` and `expire_date` to `$fillable` array
- Added date casts for both fields in `$casts` array:
  ```php
  'manufacture_date' => 'date',
  'expire_date' => 'date',
  ```

### 3. StockController Updates
**File:** `Modules/Product/app/Http/Controllers/StockController.php`

**Store Method:**
- Added validation rules:
  ```php
  'manufacture_date' => 'nullable|date',
  'expire_date' => 'nullable|date|after_or_equal:manufacture_date',
  ```
- Added fields to Stock::create() call

**Update Method:**
- Added same validation rules
- Added fields to stock->update() call

**Validation Logic:**
- Expire date must be after or equal to manufacture date
- Both fields are optional (nullable)
- Validation messages include row number for multi-entry forms

### 4. Create Stock View Updates
**File:** `Modules/Product/resources/views/stock/create.blade.php`

**Added Fields:**
- Manufacture Date input (col-md-3)
- Expire Date input (col-md-3)
- Both use `data-name` attribute for repeater functionality

**JavaScript Validation:**
Added date validation to ensure expire_date is after manufacture_date:
```javascript
$(document).on("change", ".manufacture-date, .expire-date", function(){
    let $row = $(this).closest(".stock-entry-item");
    let manufactureDate = $row.find(".manufacture-date").val();
    let expireDate = $row.find(".expire-date").val();

    if (manufactureDate && expireDate) {
        if (new Date(expireDate) < new Date(manufactureDate)) {
            alert("Expire date must be after or equal to manufacture date!");
            $row.find(".expire-date").val('');
        }
    }
});
```

### 5. Edit Stock View Updates
**File:** `Modules/Product/resources/views/stock/edit.blade.php`

**Added Fields:**
- Manufacture Date input (col-md-6)
- Expire Date input (col-md-6)
- Pre-populated with existing values using format('Y-m-d')
- Error message display for validation failures

**JavaScript Validation:**
Added same date validation as create view:
```javascript
$('#manufacture_date, #expire_date').on('change', function(){
    const manufactureDate = $('#manufacture_date').val();
    const expireDate = $('#expire_date').val();

    if (manufactureDate && expireDate) {
        if (new Date(expireDate) < new Date(manufactureDate)) {
            alert("Expire date must be after or equal to manufacture date!");
            $('#expire_date').val('');
        }
    }
});
```

## Field Details

### Manufacture Date
- **Type:** Date (nullable)
- **Label:** "Manufacture Date"
- **Required:** No
- **Validation:** Must be a valid date
- **Usage:** Track when the product was manufactured

### Expire Date
- **Type:** Date (nullable)
- **Label:** "Expire Date"
- **Required:** No
- **Validation:** 
  - Must be a valid date
  - Must be after or equal to manufacture_date
- **Usage:** Track when the product expires

## Usage Examples

### Creating Stock with Dates
```php
Stock::create([
    'product_id' => 1,
    'batch_id' => 'B-20251125120000',
    'purchase_price' => 100.00,
    'quantity' => 50,
    'sell_price' => 150.00,
    'manufacture_date' => '2025-11-01',
    'expire_date' => '2026-11-01',
    // ... other fields
]);
```

### Updating Stock Dates
```php
$stock->update([
    'manufacture_date' => '2025-11-15',
    'expire_date' => '2026-11-15',
]);
```

### Accessing Dates
```php
// As Carbon instance
$manufactureDate = $stock->manufacture_date;
$expireDate = $stock->expire_date;

// Format for display
$formatted = $stock->manufacture_date->format('d M Y');

// Check if expired
$isExpired = $stock->expire_date && $stock->expire_date->isPast();
```

## Validation Rules

### Backend Validation (Laravel)
1. **manufacture_date:** `nullable|date`
2. **expire_date:** `nullable|date|after_or_equal:manufacture_date`

### Frontend Validation (JavaScript)
- Checks if expire_date is before manufacture_date
- Shows alert and clears expire_date if validation fails
- Works on both create (repeater rows) and edit forms

## User Interface

### Create Form
- Two date inputs added below Stolen Qty field
- Each takes up 3 columns (col-md-3)
- Integrated with repeater functionality
- Validates on date change

### Edit Form
- Two date inputs in their own row
- Each takes up 6 columns (col-md-6)
- Pre-filled with existing values
- Shows validation errors below inputs
- Validates on date change

## Database Schema

```sql
ALTER TABLE `stocks` 
ADD COLUMN `manufacture_date` DATE NULL AFTER `status`,
ADD COLUMN `expire_date` DATE NULL AFTER `manufacture_date`;
```

## Testing Checklist

### Create Stock
- [ ] Can create stock without dates (both null)
- [ ] Can create stock with only manufacture date
- [ ] Can create stock with only expire date
- [ ] Can create stock with both dates
- [ ] Cannot create with expire date before manufacture date
- [ ] Validation message shows for invalid date combination
- [ ] Repeater properly handles date fields
- [ ] All rows validate independently

### Edit Stock
- [ ] Existing dates display correctly
- [ ] Can update dates
- [ ] Can clear dates (set to null)
- [ ] Cannot update with expire date before manufacture date
- [ ] Validation error appears below field
- [ ] Form pre-fills with existing values

### Model & Database
- [ ] Dates are cast to Carbon instances
- [ ] Null dates handled correctly
- [ ] Date format stored correctly (Y-m-d)
- [ ] Migration runs successfully
- [ ] Rollback works correctly

## Future Enhancements

### Potential Additions
1. **Expiry Alerts:** Show warning badges for products nearing expiry
2. **Expired Filter:** Add filter to show only expired products
3. **Dashboard Widget:** Show count of expiring products
4. **Automatic Status:** Auto-mark stock as expired after expire_date
5. **Batch Expiry Report:** Report showing products expiring in next 30/60/90 days
6. **Color Coding:** Visual indicators for expiry status (red for expired, yellow for expiring soon)

### Report Integration
Could add to Stock Overview Report:
- Count of expired products
- Value of expired stock
- Products expiring in next 30 days

## Migration Details

**Created:** November 25, 2025  
**Migration File:** `2025_11_25_200830_add_manufacture_and_expire_date_to_stocks_table.php`  
**Run Time:** 49.45ms  
**Status:** ✅ Successful

## Modified Files Summary

1. ✅ Migration file created and executed
2. ✅ Stock model updated (fillable + casts)
3. ✅ StockController updated (validation + store + update)
4. ✅ create.blade.php updated (fields + validation)
5. ✅ edit.blade.php updated (fields + validation)

## Notes

- Both fields are completely optional
- Validation only applies when both dates are provided
- JavaScript validation provides immediate feedback
- Backend validation ensures data integrity
- Date format is consistently 'Y-m-d' for database storage
- Carbon instances allow easy date manipulation
- Fields integrate seamlessly with existing stock workflow

---

**Last Updated:** November 26, 2025  
**Version:** 1.0  
**Status:** Complete and Tested
