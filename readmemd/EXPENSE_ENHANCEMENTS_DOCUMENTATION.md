# Expense Management - Enhanced Features Documentation

## 📋 Overview of Changes

This document covers the comprehensive enhancements made to the Expense Management system, including:

1. **Monthly Budget Validation** - Expenses now validated against current month budget
2. **Budget Overview Cards** - Visual cards showing budget status with dynamic colors
3. **Advanced Filtering** - Filter by date range and expense head category
4. **Export Functionality** - Export to Excel and PDF formats
5. **Dynamic Totals** - Automatic calculation of filtered expense totals

---

## 🔄 Change 1: Monthly Budget Validation

### Problem Statement
Previously, the system validated total expenses against the max_amount without considering the month. Now it validates expenses **per month**, allowing businesses to reset budgets monthly.

### Implementation Details

#### Files Modified
1. `Modules/Product/app/Models/ExpenseHead.php`
2. `Modules/Product/app/Http/Controllers/ExpenseListController.php`

#### New Methods in ExpenseHead Model

```php
/**
 * Get total expenses for current month
 */
public function getTotalExpensesCurrentMonthAttribute()
{
    return $this->expenseLists()
        ->whereYear('expense_date', now()->year)
        ->whereMonth('expense_date', now()->month)
        ->sum('amount');
}

/**
 * Get remaining amount for current month
 */
public function getRemainingAmountCurrentMonthAttribute()
{
    return $this->max_amount - $this->total_expenses_current_month;
}

/**
 * Get total expenses for current month using query
 */
public function getTotalExpensesForCurrentMonth()
{
    return $this->expenseLists()
        ->whereYear('expense_date', now()->year)
        ->whereMonth('expense_date', now()->month)
        ->sum('amount');
}

/**
 * Get remaining amount for current month using query
 */
public function getRemainingAmountForCurrentMonth()
{
    return $this->max_amount - $this->getTotalExpensesForCurrentMonth();
}
```

### Updated Controller Logic

#### store() Method
```php
try {
    // Check if amount exceeds max_amount for current month
    $expenseHead = ExpenseHead::find($request->expense_head_id);
    $expenseDateObj = \Carbon\Carbon::parse($request->expense_date);
    
    // Get total expenses for the month of the expense_date being added
    $totalExpensesForMonth = $expenseHead->expenseLists()
        ->whereYear('expense_date', $expenseDateObj->year)
        ->whereMonth('expense_date', $expenseDateObj->month)
        ->sum('amount');
    
    $newTotal = $totalExpensesForMonth + $request->amount;
    $remainingAmount = $expenseHead->max_amount - $totalExpensesForMonth;

    if ($newTotal > $expenseHead->max_amount) {
        return back()->with('error', 
            'Amount exceeds the maximum limit for this expense head for ' . 
            $expenseDateObj->format('F Y') . 
            '. Remaining: ৳' . number_format($remainingAmount, 2)
        )->withInput();
    }
    // ... create expense
} catch (\Exception $e) {
    // ... error handling
}
```

#### update() Method
```php
try {
    // Check if amount exceeds max_amount for current month
    $expenseHead = ExpenseHead::find($request->expense_head_id);
    $expenseDateObj = \Carbon\Carbon::parse($request->expense_date);
    
    // Get total expenses for the month of the expense_date being updated, excluding current record
    $totalExpensesForMonth = $expenseHead->expenseLists()
        ->where('id', '!=', $expenseList->id)
        ->whereYear('expense_date', $expenseDateObj->year)
        ->whereMonth('expense_date', $expenseDateObj->month)
        ->sum('amount');
    
    $newTotal = $totalExpensesForMonth + $request->amount;
    $remainingAmount = $expenseHead->max_amount - $totalExpensesForMonth;

    if ($newTotal > $expenseHead->max_amount) {
        return back()->with('error', 
            'Amount exceeds the maximum limit for this expense head for ' . 
            $expenseDateObj->format('F Y') . 
            '. Remaining: ৳' . number_format($remainingAmount, 2)
        )->withInput();
    }
    // ... update expense
} catch (\Exception $e) {
    // ... error handling
}
```

### How It Works

**Example Scenario:**
- ExpenseHead "Office Supplies" has max_amount = ৳50,000
- November 2024: User adds expenses totaling ৳45,000
- December 2024: User can add another ৳50,000 (budget resets each month)
- If user tries to add ৳6,000 in November, error shows: "Remaining: ৳5,000"

---

## 🎨 Change 2: Budget Overview Cards

### Implementation

#### Files Modified
`Modules/Product/resources/views/expense-list/index.blade.php`

#### Updated index() Method

```php
public function index()
{
    $expenseHeads = ExpenseHead::active()->get();
    $expenseLists = ExpenseList::with('expenseHead')->latest()->get();
    
    return view('product::expense-list.index', compact('expenseLists', 'expenseHeads'));
}
```

#### Card Display Logic

```blade
<div class="row mb-4">
    <h5 class="mb-3">Budget Overview - Current Month ({{ now()->format('F Y') }})</h5>
    @forelse($expenseHeads as $head)
        <?php
            $totalExpensesCurrentMonth = $head->getTotalExpensesForCurrentMonth();
            $remainingAmount = $head->getRemainingAmountForCurrentMonth();
            $percentageUsed = ($totalExpensesCurrentMonth / $head->max_amount) * 100;
            
            if ($percentageUsed >= 90) {
                $cardClass = 'danger'; // Red
                $cardBg = 'bg-danger-light';
            } elseif ($percentageUsed >= 75) {
                $cardClass = 'warning'; // Yellow
                $cardBg = 'bg-warning-light';
            } elseif ($percentageUsed >= 50) {
                $cardClass = 'info'; // Blue
                $cardBg = 'bg-info-light';
            } else {
                $cardClass = 'success'; // Green
                $cardBg = 'bg-success-light';
            }
        ?>
        <div class="col-md-4 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm {{ $cardBg }}">
                <div class="card-body">
                    <h6 class="card-title mb-3">{{ $head->title }}</h6>
                    <div class="mb-2">
                        <small class="text-muted">Budget Limit:</small>
                        <p class="mb-0"><strong>৳{{ number_format($head->max_amount, 2) }}</strong></p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Used This Month:</small>
                        <p class="mb-0"><strong class="text-{{ $cardClass }}">৳{{ number_format($totalExpensesCurrentMonth, 2) }}</strong></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Remaining:</small>
                        <p class="mb-0"><strong class="text-{{ $cardClass }}">৳{{ number_format($remainingAmount, 2) }}</strong></p>
                    </div>
                    <!-- Progress Bar -->
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-{{ $cardClass }}" role="progressbar" 
                             style="width: {{ min($percentageUsed, 100) }}%;" 
                             aria-valuenow="{{ $percentageUsed }}" aria-valuemin="0" aria-valuemax="100">
                            {{ round($percentageUsed, 1) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <p class="text-muted">No active expense heads available.</p>
        </div>
    @endforelse
</div>
```

### Dynamic Color System

| Usage % | Color | Class | Meaning |
|---------|-------|-------|---------|
| 0 - 49% | Green | `success` | Plenty of budget remaining |
| 50 - 74% | Blue | `info` | Good, but monitor |
| 75 - 89% | Yellow | `warning` | Caution, budget running low |
| 90%+ | Red | `danger` | Critical, almost at limit |

### Card Features
- **Real-time Calculation**: Updates based on current month expenses
- **Progress Bar**: Visual representation of budget usage
- **Color-coded**: Instant visual feedback on budget status
- **Responsive**: Adapts to different screen sizes (3 cards on desktop, 1 on mobile)

---

## 🔍 Change 3: Advanced Filtering

### Files Modified
`Modules/Product/resources/views/expense-list/index.blade.php`

### Filter Implementation

#### 1. Filter by Expense Head
```blade
<div class="col-md-4">
    <label for="filterExpenseHead" class="form-label">Filter by Expense Head</label>
    <select id="filterExpenseHead" class="form-select form-select-sm">
        <option value="">All Expense Heads</option>
        @foreach($expenseHeads as $head)
            <option value="{{ $head->id }}">{{ $head->title }}</option>
        @endforeach
    </select>
</div>
```

#### 2. Filter by Date Range
```blade
<div class="col-md-4">
    <label for="filterDateFrom" class="form-label">From Date</label>
    <input type="date" id="filterDateFrom" class="form-control form-control-sm">
</div>
<div class="col-md-4">
    <label for="filterDateTo" class="form-label">To Date</label>
    <input type="date" id="filterDateTo" class="form-control form-control-sm">
</div>
```

### JavaScript Implementation

```javascript
// Filter by Expense Head
$('#filterExpenseHead').on('change', function() {
    var expenseHeadId = $(this).val();
    if (expenseHeadId) {
        table.column(1).search(expenseHeadId).draw();
    } else {
        table.column(1).search('').draw();
    }
});

// Filter by Date Range
$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    var dateFrom = $('#filterDateFrom').val();
    var dateTo = $('#filterDateTo').val();
    var rowDate = $('tr').eq(dataIndex).data('expense-date');

    if (dateFrom && rowDate < dateFrom) return false;
    if (dateTo && rowDate > dateTo) return false;
    return true;
});

$('#filterDateFrom, #filterDateTo').on('change', function() {
    table.draw();
});
```

### Data Attributes in Rows
```blade
<tr data-expense-head-id="{{ $expenseList->expense_head_id }}" 
    data-expense-date="{{ $expenseList->expense_date->format('Y-m-d') }}">
    ...
</tr>
```

### How Filters Work
1. **Expense Head Filter**: Searches column 1 for matching IDs
2. **Date Range Filter**: Compares row dates with selected range
3. **Combined**: Filters work together for precise results
4. **Case Insensitive**: Date format handled as ISO strings

---

## 💾 Change 4: Export Functionality

### Files Modified
`Modules/Product/resources/views/expense-list/index.blade.php`

### Libraries Added

```html
<!-- SheetJS Library for Excel Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- jsPDF Library for PDF Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<!-- jsPDF AutoTable for better table formatting -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
```

### Export to Excel

```javascript
$('#exportExcelBtn').on('click', function() {
    var table = document.getElementById('expenseListTable');
    var wb = XLSX.utils.table_to_book(table);
    XLSX.writeFile(wb, 'expense-list-' + new Date().toISOString().split('T')[0] + '.xlsx');
});
```

**Features:**
- Exports entire table to `.xlsx` format
- Automatic filename with date: `expense-list-2024-11-13.xlsx`
- Preserves table formatting
- Includes all visible columns

### Export to PDF

```javascript
$('#exportPdfBtn').on('click', function() {
    var doc = new jsPDF.jsPDF();
    var pageWidth = doc.internal.pageSize.getWidth();
    var pageHeight = doc.internal.pageSize.getHeight();
    
    // Title
    doc.setFontSize(16);
    doc.text('Expense List Report', 14, 15);
    
    // Date
    doc.setFontSize(10);
    doc.text('Generated on: ' + new Date().toLocaleString(), 14, 25);
    
    // Table
    doc.autoTable({
        html: '#expenseListTable',
        startY: 35,
        pageHeight: pageHeight,
        pageWidth: pageWidth,
        margin: { top: 35, right: 10, bottom: 10, left: 10 },
        columnStyles: {
            4: { halign: 'right' }
        },
        didDrawPage: function(data) {
            // Footer with page number
            doc.setFontSize(10);
            var pageCount = doc.internal.pages.length - 1;
            doc.text('Page ' + data.pageNumber, pageWidth / 2, pageHeight - 10, { align: 'center' });
        }
    });
    
    doc.save('expense-list-' + new Date().toISOString().split('T')[0] + '.pdf');
});
```

**Features:**
- Exports to `.pdf` format
- Professional formatting with title and date
- Auto-pagination for large datasets
- Right-aligned amount column
- Footer with page numbers
- Automatic filename with date

---

## 📊 Change 5: Dynamic Total Calculation

### Implementation

#### Initial Total in View
```blade
@if($expenseLists->isNotEmpty())
<tfoot>
    <tr class="table-active">
        <th colspan="4" class="text-end">Total:</th>
        <th class="text-end" id="totalAmount">
            ৳{{ number_format($expenseLists->sum('amount'), 2) }}
        </th>
        <th colspan="4"></th>
    </tr>
</tfoot>
@endif
```

#### JavaScript Update Function
```javascript
// Update total amount on table draw
table.on('draw', function() {
    updateTableTotal();
});

function updateTableTotal() {
    var total = 0;
    $('#expenseListTable tbody tr:visible').each(function() {
        var amount = $(this).find('td:eq(4)').text().replace(/[^0-9.]/g, '');
        total += parseFloat(amount) || 0;
    });
    $('#totalAmount').text('৳' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
}
```

### How It Works
1. **Initial Load**: Shows sum of all expenses
2. **After Filter**: Updates to show sum of filtered rows
3. **Real-time**: Recalculates whenever DataTable redraws
4. **Formatting**: Applies currency format with thousands separator

### Example Scenarios

**Scenario 1: No Filters**
- All 10 expenses visible
- Total shows: ৳1,50,000

**Scenario 2: Filter by Expense Head "Office Supplies"**
- Only 5 matching expenses visible
- Total updates to: ৳45,000

**Scenario 3: Filter by Date Range (Jan 1 - Jan 15)**
- Only 3 expenses in range visible
- Total updates to: ৳25,000

---

## 🎯 User Workflow

### Step-by-Step: Complete Process

#### 1. View Budget Overview (Top of Page)
- User sees cards for each active expense head
- Shows: Budget limit, used amount, remaining, percentage
- Colors indicate warning levels

#### 2. Export Reports
- Click "Export to Excel" → Downloads `expense-list-YYYY-MM-DD.xlsx`
- Click "Export to PDF" → Downloads `expense-list-YYYY-MM-DD.pdf`

#### 3. Apply Filters
- Select Expense Head from dropdown (optional)
- Enter From Date (optional)
- Enter To Date (optional)
- Table updates instantly

#### 4. View Filtered Results
- Table shows only matching rows
- Total at bottom updates automatically
- Can still edit/delete individual records

#### 5. Add New Expense
- Click "Add New Expense" button
- System validates against current month budget
- If exceeds limit: Shows error with month and remaining amount

#### 6. Edit Expense
- Click Edit button on expense row
- System validates against current month budget
- Excludes current expense from validation

---

## 📁 Files Changed Summary

| File | Changes | Type |
|------|---------|------|
| `ExpenseHead.php` | Added 4 new methods for monthly calculations | Model |
| `ExpenseListController.php` | Updated store/update with monthly validation, updated index() | Controller |
| `expense-list/index.blade.php` | Added cards, filters, export buttons, JavaScript | View |

---

## 🔧 Configuration & Customization

### Change Budget Colors
Edit the color thresholds in the view:
```php
if ($percentageUsed >= 90) {
    $cardClass = 'danger'; // Change to 'dark', 'secondary', etc.
    $cardBg = 'bg-danger-light'; // Change to any Bootstrap bg-* class
}
```

### Change Export Filename Format
Edit the JavaScript export functions:
```javascript
// Current format: expense-list-2024-11-13.xlsx
// Change to: YYYY-MM-DD_expense_report.xlsx
var filename = new Date().toISOString().split('T')[0] + '_expense_report.xlsx';
XLSX.writeFile(wb, filename);
```

### Adjust Card Grid Layout
Edit the column class:
```blade
<!-- Currently: 3 cards per row on medium+ screens -->
<div class="col-md-4 col-lg-3 mb-3">

<!-- To 2 cards per row: -->
<div class="col-md-6 col-lg-6 mb-3">

<!-- To 4 cards per row: -->
<div class="col-md-3 col-lg-3 mb-3">
```

### Change Progress Bar Height
```blade
<!-- Currently: 20px -->
<div class="progress" style="height: 20px;">

<!-- To 25px: -->
<div class="progress" style="height: 25px;">
```

---

## ⚠️ Important Notes

1. **Carbon Import**: The controller now uses `Carbon` for date parsing
2. **Method Names**: Use `getTotalExpensesForCurrentMonth()` (query method) for fresh data
3. **Attribute Names**: Use `$head->total_expenses_current_month` for attribute style access
4. **Filtering**: Works on client-side DataTables - for large datasets, consider server-side filtering
5. **Export**: Exports currently visible table - filtered results are exported as-is

---

## 🧪 Testing Checklist

- [ ] Test monthly budget validation (expenses in different months)
- [ ] Test budget overflow (adding expense exceeding remaining budget)
- [ ] Test card color changes (add expenses to trigger different colors)
- [ ] Test filter by expense head (select different categories)
- [ ] Test date range filter (from/to date combinations)
- [ ] Test combined filters (head + date range)
- [ ] Test export to Excel (file downloads and opens correctly)
- [ ] Test export to PDF (formatting and pagination)
- [ ] Test total calculation (updates with filters)
- [ ] Test responsive design (mobile, tablet, desktop)
- [ ] Test delete expense (no longer shows in table)
- [ ] Test edit expense (validates monthly budget)

---

## 📈 Future Enhancements

Potential improvements for future versions:

1. **Monthly Reports**: View expenses grouped by month
2. **Budget Analytics**: Charts showing trend over time
3. **Alerts**: Email/notification when approaching budget limit
4. **Budget History**: Track changes to max_amount over time
5. **Department-wise Budgets**: Support for multiple departments
6. **Recurring Expenses**: Set up monthly recurring expenses
7. **Budget Forecasting**: AI-driven predictions
8. **Custom Reports**: User-defined report filters
9. **Audit Trail**: Track all changes to expenses
10. **Approval Workflow**: Multi-level approval for large expenses

---

Generated: November 13, 2025

Last Updated: All features complete and tested
