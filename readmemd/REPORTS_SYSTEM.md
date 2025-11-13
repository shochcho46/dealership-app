# Report System Documentation

## Overview
This document describes the reporting system implemented in the dealership application, consisting of three comprehensive reports for business insights.

## Reports

### 1. Stock Overview Report

**Route:** `admin/report/stock-overview`  
**Controller:** `ReportController@stockOverview`  
**Menu Location:** Stock Management > Stock Overview

#### Features
- Shows lifetime statistics for every product
- **Columns:**
  - Product Image
  - Product Name
  - Total Purchase Quantity
  - Total Purchase Amount
  - Total Sold Quantity
  - Total Sold Amount
  - Available Quantity
  - Available Amount

#### Calculations
- **Total Purchase:** Sum of all stock quantities and their purchase values
- **Total Sold:** Sum of all sold quantities from order items and their sell values
- **Available:** Current stock minus sold, damaged, stolen, and frozen quantities
- Footer shows grand totals for all columns

#### Features
- DataTables integration for sorting and searching
- Export to Excel functionality
- Responsive table design
- Grand total row in footer

---

### 2. Order Report

**Route:** `admin/report/order-report`  
**Controller:** `ReportController@orderReport`  
**Menu Location:** Reports > Order Report

#### Features
- Comprehensive order item details with profit analysis
- **Filters:**
  - Date Range (From/To)
  - Vendor
  - Product (shows only that product across all orders)
  - Placed By (admin user)

#### Columns
1. Order Number (with link to invoice)
2. Order Date
3. Vendor Name
4. Product Name
5. Placed By (admin user)
6. Quantity
7. Purchase Price (per unit, averaged)
8. Total Purchase Amount
9. Sell Price (per unit)
10. Total Sell Amount
11. Discount
12. Profit

#### Special Behavior
- When filtering by product: Shows that specific product from all orders
- If an order has multiple items but filter selects one product, only that product's row appears
- Footer displays sum of quantities, total purchase, total sell, total discount, and total profit

#### Export
- Export to Excel button
- Preserves all filters in exported data

---

### 3. Profitable Product Report

**Route:** `admin/report/profitable-product`  
**Controller:** `ReportController@profitableProduct`  
**Menu Location:** Reports > Profitable Products

#### Features
- Ranks products by profitability
- Only shows products that have been sold
- **Filters:**
  - Date Range (From/To)

#### Columns
1. Rank (Top 3 highlighted with gold/silver/bronze badges)
2. Product Image
3. Product Name
4. Total Sold Quantity
5. Total Revenue
6. Total Cost
7. Total Profit (highlighted in green)
8. Profit Margin % (color-coded badge)
9. Status (Excellent/Good/Average/Low)

#### Profit Margin Indicators
- **Excellent (Green):** ≥30% profit margin
- **Good (Blue):** ≥20% profit margin
- **Average (Yellow):** ≥10% profit margin
- **Low (Red):** <10% profit margin

#### Ordering
- Default: Sorted by total profit (highest to lowest)
- Shows most profitable products first

#### Summary Cards
Below the table, four cards display:
1. Total Products Sold
2. Total Revenue
3. Total Cost
4. Total Profit

#### Business Logic
- Only includes orders with status "Shipped" or "Delivered" (status IDs 4 or 5)
- Calculates actual profit after discounts
- Filters out products with zero sales

---

## Technical Implementation

### Controller: `ReportController.php`

#### Methods

**1. stockOverview()**
```php
public function stockOverview()
```
- Retrieves all products with stock relationships
- Calculates purchase, sold, and available metrics
- Returns array of product statistics

**2. orderReport(Request $request)**
```php
public function orderReport(Request $request)
```
- Accepts filter parameters
- Queries OrderItem with relationships
- Calculates item-level purchase prices and profits
- Returns filtered order items and totals

**3. profitableProduct(Request $request)**
```php
public function profitableProduct(Request $request)
```
- Filters by date range
- Only includes shipped/delivered orders
- Maps products to profit statistics
- Sorts by profit descending

### Views

**1. stock-overview.blade.php**
- Responsive table with DataTables
- Number formatting for currency
- Image thumbnails
- Excel export functionality

**2. order-report.blade.php**
- Filter form with Select2 dropdowns
- Linked order numbers to invoices
- Date formatting
- Footer totals row

**3. profitable-product.blade.php**
- Rank badges for top 3 products
- Color-coded profit margins
- Summary cards
- Status indicators

### Routes

Added to `Modules/Product/routes/web.php`:

```php
Route::controller(ReportController::class)->group(function () {
    Route::get('report/stock-overview', 'stockOverview')->name('admin.reportStockOverview');
    Route::get('report/order-report', 'orderReport')->name('admin.reportOrderReport');
    Route::get('report/profitable-product', 'profitableProduct')->name('admin.reportProfitableProduct');
});
```

### Sidebar Integration

**Stock Overview:** Added under Stock Management section  
**Order Report & Profitable Products:** New REPORTS section between Return Management and Financial Management

---

## Usage Guide

### Stock Overview
1. Navigate to Stock Management > Stock Overview
2. View comprehensive lifetime statistics for all products
3. Use DataTables search to find specific products
4. Export to Excel for external analysis

### Order Report
1. Navigate to Reports > Order Report
2. Apply filters as needed:
   - Select date range for specific period
   - Choose vendor to see orders from specific supplier
   - Filter by product to see that product across all orders
   - Filter by admin user who placed orders
3. Click Filter button to apply
4. Review detailed breakdown with profit calculations
5. Export to Excel if needed

### Profitable Product Report
1. Navigate to Reports > Profitable Products
2. Optionally select date range
3. Review products ranked by profitability
4. Identify best-performing products (top 3 highlighted)
5. Check profit margins and status indicators
6. Review summary cards for overall performance

---

## Database Relationships Used

### Stock Overview
- Product → hasMany → Stock
- Stock columns: quantity, sold_quantity, damage_quantity, stolen_quantity, froze_quantity, purchase_price, sell_price

### Order Report
- OrderItem → belongsTo → Order
- OrderItem → belongsTo → Product
- OrderItem → hasMany → OrderItemStock
- Order → belongsTo → Vendor
- Order → belongsTo → Admin (placeBy)

### Profitable Product
- Product → hasMany → OrderItem
- OrderItem → hasMany → OrderItemStock
- OrderItem → belongsTo → Order (for status and date filtering)

---

## Export Functionality

All reports include Export to Excel feature using `table2excel` library:

```javascript
function exportTableToExcel(tableID, filename = '') {
    var table2excel = new Table2Excel();
    table2excel.export(document.getElementById(tableID), filename);
}
```

Exported files maintain:
- All visible columns
- Formatting (numbers, currency)
- Footer totals
- Applied filters (reflected in data)

---

## Performance Considerations

### Stock Overview
- Uses eager loading: `Product::with(['stocks'])`
- Calculations performed in memory after data retrieval
- Suitable for up to ~1000 products

### Order Report
- Uses query builder with `whereHas` for filtering
- Eager loads all necessary relationships
- Pagination not implemented (assumes manageable dataset)
- Consider adding pagination if order items exceed 10,000

### Profitable Product
- Filters in-memory after loading products
- Only counts shipped/delivered orders
- Removes zero-sale products before display
- Sorted by profit in memory

---

## Future Enhancements

### Potential Improvements
1. **Pagination:** Add pagination to all reports for large datasets
2. **Charts:** Add visual charts using Chart.js
3. **Scheduled Reports:** Email reports automatically
4. **PDF Export:** Add PDF export alongside Excel
5. **Custom Date Ranges:** Add preset ranges (This Week, Last Month, etc.)
6. **Cache Results:** Cache report data for frequently accessed periods
7. **Print Preview:** Add print-friendly versions
8. **Column Visibility:** Allow users to show/hide columns
9. **More Filters:** Add product category, brand, warehouse filters
10. **Comparison:** Compare periods (e.g., this month vs last month)

---

## Troubleshooting

### Common Issues

**No data showing:**
- Check if products/orders exist in database
- Verify date ranges are correct
- Ensure order status is shipped/delivered for profitable products

**Export not working:**
- Verify table2excel library is loaded
- Check browser console for JavaScript errors
- Ensure table has an ID attribute

**Incorrect totals:**
- Verify stock calculations include all status types
- Check OrderItemStock relationship and data integrity
- Ensure discount calculations are correct

**Performance slow:**
- Add database indexes on frequently filtered columns
- Implement pagination
- Cache report results

---

## Testing Checklist

### Stock Overview
- [ ] All products display correctly
- [ ] Purchase calculations match stock data
- [ ] Sold quantities match order data
- [ ] Available quantities are accurate
- [ ] Footer totals sum correctly
- [ ] Export to Excel works
- [ ] DataTables search and sort work

### Order Report
- [ ] All filters work independently
- [ ] Date range filter works correctly
- [ ] Vendor filter shows correct orders
- [ ] Product filter shows only that product
- [ ] Place By filter works
- [ ] Multiple filters work together
- [ ] Profit calculations are accurate
- [ ] Footer totals are correct
- [ ] Export includes filtered data

### Profitable Product
- [ ] Products sorted by profit
- [ ] Date filter works correctly
- [ ] Only sold products appear
- [ ] Top 3 ranks are highlighted
- [ ] Profit margin badges show correct colors
- [ ] Status indicators match thresholds
- [ ] Summary cards show correct totals
- [ ] Export works correctly

---

## Created Files

1. `Modules/Product/app/Http/Controllers/ReportController.php`
2. `Modules/Product/resources/views/reports/stock-overview.blade.php`
3. `Modules/Product/resources/views/reports/order-report.blade.php`
4. `Modules/Product/resources/views/reports/profitable-product.blade.php`

## Modified Files

1. `Modules/Product/routes/web.php` - Added report routes
2. `resources/views/layouts/adminsidebar.blade.php` - Added menu items

---

**Created:** November 14, 2025  
**Version:** 1.0  
**Author:** Development Team
