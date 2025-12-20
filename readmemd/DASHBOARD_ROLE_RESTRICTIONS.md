# Dashboard Role-Based Restrictions & SR/DSR Performance Charts

## Overview
The dashboard has been enhanced with role-based access restrictions and two new performance tracking charts for Sales Representatives (SR) and District Sales Representatives (DSR).

## Features Implemented

### 1. Role-Based View Restrictions

#### Restricted Roles: `dsr` and `sr`
These users can **ONLY** see:
- ✅ Sales Overview Chart
- ✅ Top Selling Products Chart
- ✅ Recent Orders Table
- ✅ Best Sellers Table
- ✅ Orders by Sales Team Chart (NEW)
- ✅ Collection by Sales Team Chart (NEW)

#### Hidden from DSR/SR:
- ❌ All Statistics Cards (Income, Expenses, Revenue, Profit, Products, Stock Qty, Stock Value, Pending Payments)
- ❌ Income Trend Chart
- ❌ Profit Trend Chart
- ❌ Expenses by Category Chart
- ❌ Stock by Warehouse Chart
- ❌ Damage/Return/Lost Summary

#### Full Access (Admin, Manager, etc.)
All other roles see the complete dashboard with all statistics and charts.

---

## 2. New Performance Charts

### A. Orders by Sales Team Chart
**Location:** Below Top Selling Products section  
**Type:** Bar Chart  
**Purpose:** Track daily/monthly/yearly order count by each SR/DSR user

**Features:**
- Shows order count per sales representative
- Supports Daily, Monthly, Yearly periods
- Date filtering (Today, Week, Month, Year, Custom Range)
- **For DSR/SR users:** Shows only their own orders
- **For Admin:** Shows all DSR/SR users' orders grouped by user
- AJAX loading (non-blocking)

**Data Source:** Orders table, filtered by `created_by` field  
**Chart Color:** Purple gradient (#667eea to #764ba2)

### B. Collection by Sales Team Chart
**Location:** Next to Orders by Sales Team  
**Type:** Line Chart with gradient fill  
**Purpose:** Track daily/monthly/yearly collection amount by each SR/DSR user

**Features:**
- Shows paid amount collected per sales representative
- Supports Daily, Monthly, Yearly periods
- Date filtering (Today, Week, Month, Year, Custom Range)
- **For DSR/SR users:** Shows only their own collection
- **For Admin:** Shows all DSR/SR users' collections grouped by user
- AJAX loading (non-blocking)
- Currency formatted (৳)

**Data Source:** Orders table, `paid_amount` field, filtered by `created_by`  
**Chart Color:** Pink gradient (#f093fb to #f5576c)

---

## Technical Implementation

### Routes Added
```php
// routes/admin.php
Route::get('dashboard/charts/user-orders', 'getUserOrdersChartData')
    ->name('admin.dashboard.charts.userOrders');

Route::get('dashboard/charts/user-collection', 'getUserCollectionChartData')
    ->name('admin.dashboard.charts.userCollection');
```

### Controller Methods Added

#### `getUserOrdersChartData(Request $request)`
- Retrieves order count by SR/DSR users
- Groups by period (day/month/year)
- Filters by current user for DSR/SR roles
- Returns labels and order counts

#### `getUserCollectionChartData(Request $request)`
- Retrieves collection amount by SR/DSR users
- Groups by period (day/month/year)
- Filters by current user for DSR/SR roles
- Returns labels and collection amounts

### View Changes

#### Role Detection
```php
@php
    $userRoles = Auth::guard('admin')->user()->getRoleNames();
    $isRestrictedUser = $userRoles->contains('dsr') || $userRoles->contains('sr');
@endphp
```

#### Conditional Rendering
```blade
@if(!$isRestrictedUser)
    <!-- Full dashboard content -->
@endif

<!-- Always visible content -->
```

#### JavaScript Chart Loading
```javascript
const isRestrictedUser = {{ $isRestrictedUser ? 'true' : 'false' }};

function loadUserPerformanceCharts() {
    // Load Orders Chart via AJAX
    // Load Collection Chart via AJAX
}
```

---

## Database Schema Reference

### Orders Table Fields Used:
- `created_by` - Admin user who created the order
- `created_at` - Order date for grouping
- `paid_amount` - Collection amount

### Admin Roles:
Uses **Spatie Laravel Permission** package
- Roles: `admin`, `manager`, `dsr`, `sr`, etc.
- Method: `getRoleNames()` returns collection of role names

---

## Usage Instructions

### For Admins:
1. Navigate to Dashboard
2. See complete statistics and all charts
3. New performance charts show aggregated data for all SR/DSR users
4. Use filters to analyze team performance by date ranges

### For DSR/SR Users:
1. Navigate to Dashboard
2. Limited view with essential sales information only
3. Performance charts show personal metrics only
4. Track daily orders and collection performance

### Filtering:
- **Date Range:** All Time, Today, This Week, This Month, This Year, Custom Range
- **Chart Period:** Daily, Monthly, Yearly
- All charts refresh when filters change
- AJAX loading ensures smooth user experience

---

## Chart Configuration

### Orders by Sales Team
```javascript
{
    type: 'bar',
    colors: ['#667eea'],
    dataLabels: { enabled: true },
    title: 'Daily Orders Count'
}
```

### Collection by Sales Team
```javascript
{
    type: 'line',
    colors: ['#f5576c'],
    stroke: { curve: 'smooth', width: 3 },
    fill: { type: 'gradient' },
    title: 'Daily Collection Amount (৳)',
    yaxis: { formatter: '৳{value}' }
}
```

---

## Security Considerations

1. **Role-Based Access:** Uses Laravel middleware `auth:admin` + Spatie Permissions
2. **Data Isolation:** DSR/SR users can only see their own performance data
3. **Query Filtering:** Controller methods check user roles before data retrieval
4. **No Client-Side Bypass:** Role checks happen server-side in controller

---

## Testing Checklist

- [ ] Login as Admin - Verify full dashboard visible
- [ ] Login as DSR - Verify restricted view (limited sections)
- [ ] Login as SR - Verify restricted view (limited sections)
- [ ] Check Orders chart shows correct data for current user (DSR/SR)
- [ ] Check Collection chart shows correct data for current user (DSR/SR)
- [ ] Verify Admin sees all DSR/SR users in performance charts
- [ ] Test date filters work correctly
- [ ] Test period selection (Daily/Monthly/Yearly)
- [ ] Verify AJAX loading works (no page refresh)
- [ ] Check all charts render properly

---

## Files Modified

1. **routes/admin.php**
   - Added 2 new routes for performance charts

2. **app/Http/Controllers/Admin/AdminController.php**
   - Added `getUserOrdersChartData()` method
   - Added `getUserCollectionChartData()` method

3. **resources/views/admin/dashboard.blade.php**
   - Added role detection logic
   - Added conditional rendering for restricted sections
   - Added 2 new performance chart sections
   - Updated JavaScript to load new charts
   - Added conditional chart loading based on role

---

## Future Enhancements

1. **Commission Tracking:** Add chart for commission calculations per SR/DSR
2. **Target vs Actual:** Compare monthly targets with actual performance
3. **Leaderboard:** Rank SR/DSR by performance metrics
4. **Customer Acquisition:** Track new customers added by each SR/DSR
5. **Product-wise Performance:** Show which products each SR/DSR sells most

---

## Troubleshooting

### Charts Not Loading
- Check browser console for JavaScript errors
- Verify routes are accessible: `/admin/dashboard/charts/user-orders`
- Check Laravel logs for controller errors

### Wrong Data Displayed
- Verify user has correct role assigned (dsr/sr)
- Check `created_by` field in Orders table
- Ensure Spatie Permissions are seeded properly

### Performance Issues
- Add database indexes on `created_by`, `created_at` columns
- Consider caching dashboard statistics for large datasets
- Use queue jobs for heavy calculations

---

## Contact & Support

For issues or questions about dashboard functionality, contact the development team.
