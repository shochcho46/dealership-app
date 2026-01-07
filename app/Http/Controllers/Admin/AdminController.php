<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ForgetPassMail;
use App\Models\Admin;
use App\Models\Country;
use App\Models\Gender;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator ;
use Modules\Admin\Entities\Account;
use Modules\Admin\Entities\Business;
use Modules\Admin\Entities\Category;
use Modules\Product\Models\Order;
use Modules\Product\Models\OrderItem;
use Modules\Product\Models\Product;
use Modules\Product\Models\Stock;
use Modules\Product\Models\VendorAccount;
use Modules\Product\Models\ExpenseList;
use Modules\Product\Models\CompanyOrder;
use Modules\Product\Models\InvestmentDetail;
use Modules\Product\Models\DamageReturnLost;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;

class AdminController extends Controller
{

    public function adminLogin()
    {
        $datas = Country::all();
        $genders = Gender::all();
        $busineesSetting = Business::first();
        return view('auth.admin.login',compact('datas','genders','busineesSetting'));

    }

    public function loadForgetMyPass()
    {
        $datas = Country::all();
        return view('auth.admin.forget',compact('datas'));

    }

    public function findUser(Request $request)
    {
        $countryIso = Country::where('id',18)->first();

        $validated = $request->validate([
            'email_or_phone' => ['bail','required'],
            ],
            [
                'email_or_phone.regex' => 'The phone number must contain only English digits (0-9).',
                'email_or_phone.required' => 'The phone number is required',
            ]
        );

        if (filter_var($request->email_or_phone, FILTER_VALIDATE_EMAIL)) {
            $credential = array("email" => $request->email_or_phone);
        }
        else
        {
            $phoneNumber = validationMobileNumber($request->email_or_phone,$countryIso->iso);
            $credential = array("phone" => $phoneNumber);
            $email = false;
        }

        $admin = Admin::where($credential)->first();

        if ($admin) {
            $toster = array(
                'message' => 'User Found',
                'alert-type' => 'success'
            );

            return redirect()->route('otpLoad')->with('uuid', $admin->id)->with($toster);

        }
        else
        {
            $toster = array(
                'message' => 'User Not Found',
                'alert-type' => 'error'
            );

            return back()->with( $toster);
        }
    }



    public function adminValidateLogin(Request $request)
    {

        $countryIso = Country::where('id',18)->first();

        $validated = $request->validate([
            'email_or_phone' => ['bail','required'],
            'password' => 'required',
            ],
            [
                'email_or_phone.regex' => 'The phone number must contain only English digits (0-9).',
                'email_or_phone.required' => 'The phone number is required',
            ]
        );

        if (filter_var($request->email_or_phone, FILTER_VALIDATE_EMAIL)) {

            $credential = array("email" => $request->email_or_phone, "password" => $request->password);
        }
        else
        {
            $phoneNumber = validationMobileNumber($request->email_or_phone,$countryIso->iso);
            $credential = array("phone" => $phoneNumber, "password" => $request->password);
        }

        if (Auth::guard('admin')->attempt($credential)) {

            $user = Auth::guard('admin')->user();

            if (($user->status == 0)) {

                $toster = array(
                    'message' => 'This account is in black listed',
                    'alert-type' => 'error'
                );

                return back()->with( $toster);
            } else {

                return redirect()->route('admin.dashboard');
            }

        }


        else
        {
            $toster = array(
                'message' => 'Wrong Credential',
                'alert-type' => 'error'
            );

            return back()->with( $toster);

        }
    }
    public function dashboard()
    {
        // Return view without data - data will be loaded via AJAX
        return view('admin.dashboard');
    }

    /**
     * Get dashboard statistics (AJAX)
     */
    public function getDashboardStats(Request $request)
    {
        try {
            $dateRange = $this->getDateRange($request);

            // Calculate Total Income (from vendor payments - Type 2 = Credit)
            $totalIncome = VendorAccount::where('type', 2)
                ->when($dateRange['start'], fn($q) => $q->whereDate('created_at', '>=', $dateRange['start']))
                ->when($dateRange['end'], fn($q) => $q->whereDate('created_at', '<=', $dateRange['end']))
                ->sum('amount');

            // Calculate Total Expenses
            $totalExpenses = ExpenseList::active()
                ->when($dateRange['start'], fn($q) => $q->whereDate('expense_date', '>=', $dateRange['start']))
                ->when($dateRange['end'], fn($q) => $q->whereDate('expense_date', '<=', $dateRange['end']))
                ->sum('amount');

            // Add Company Order expenses (money going out)
            // $companyOrderExpenses = CompanyOrder::when($dateRange['start'], fn($q) => $q->whereDate('created_at', '>=', $dateRange['start']))
            //     ->when($dateRange['end'], fn($q) => $q->whereDate('created_at', '<=', $dateRange['end']))
            //     ->sum('paid_amount');

            // $totalExpenses += $companyOrderExpenses;

            // // Add Investment expenses
            // $investmentExpenses = InvestmentDetail::when($dateRange['start'], fn($q) => $q->whereDate('investment_date', '>=', $dateRange['start']))
            //     ->when($dateRange['end'], fn($q) => $q->whereDate('investment_date', '<=', $dateRange['end']))
            //     ->sum('amount');

            // $totalExpenses += $investmentExpenses;

            // Calculate Total Profit from Orders
            $totalProfit = Order::with('orderItems.orderItemStocks')
                ->when($dateRange['start'], fn($q) => $q->whereDate('created_at', '>=', $dateRange['start']))
                ->when($dateRange['end'], fn($q) => $q->whereDate('created_at', '<=', $dateRange['end']))
                ->get()
                ->sum(function ($order) {
                    return $order->orderItems->sum(function ($item) {
                        return $item->orderItemStocks->sum(function ($stock) {
                            return ($stock->actual_profit ?? 0) - ($stock->discount_amount ?? 0);
                        });
                    });
                });

            // Calculate Revenue (Income - Expenses)
            $revenue = $totalIncome - $totalExpenses;

            // Total Products
            $totalProducts = Product::count();

            // Total Available Stock Quantity (quantity - sold - damage - stolen - froze)
            $totalStockQuantity = Stock::selectRaw('SUM(quantity - sold_quantity - damage_quantity - stolen_quantity - froze_quantity) as available_qty')->value('available_qty') ?? 0;

            // Total Available Stock Value (using purchase price)
            $totalStockValue = Stock::selectRaw('SUM((quantity - sold_quantity - damage_quantity - stolen_quantity - froze_quantity) * purchase_price) as total_value')->value('total_value') ?? 0;

            // Total Orders
            $totalOrders = Order::when($dateRange['start'], fn($q) => $q->whereDate('created_at', '>=', $dateRange['start']))
                ->when($dateRange['end'], fn($q) => $q->whereDate('created_at', '<=', $dateRange['end']))
                ->count();

            // Pending Payments (Balance Due from Vendors)
            $pendingPayments = VendorAccount::selectRaw('
                    vendor_id,
                    SUM(CASE WHEN type = 1 THEN amount ELSE 0 END) as total_debit,
                    SUM(CASE WHEN type = 2 THEN amount ELSE 0 END) as total_credit
                ')
                ->groupBy('vendor_id')
                ->get()
                ->sum(function ($account) {
                    return $account->total_debit - $account->total_credit;
                });

            // Damage/Lost Statistics
            $damageStats = DamageReturnLost::selectRaw('
                    COUNT(*) as total_count,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as damage_count,
                    SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as return_count,
                    SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as lost_count
                ')
                ->when($dateRange['start'], fn($q) => $q->whereDate('created_at', '>=', $dateRange['start']))
                ->when($dateRange['end'], fn($q) => $q->whereDate('created_at', '<=', $dateRange['end']))
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'income' => number_format($totalIncome, 2),
                    'expenses' => number_format($totalExpenses, 2),
                    'revenue' => number_format($revenue, 2),
                    'profit' => number_format($totalProfit, 2),
                    'products' => $totalProducts,
                    'stock_quantity' => number_format($totalStockQuantity),
                    'stock_value' => number_format($totalStockValue, 2),
                    'total_orders' => $totalOrders,
                    'pending_payments' => number_format($pendingPayments, 2),
                    'damage_count' => $damageStats->damage_count ?? 0,
                    'return_count' => $damageStats->return_count ?? 0,
                    'lost_count' => $damageStats->lost_count ?? 0,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard Stats Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error loading statistics'], 500);
        }
    }

    /**
     * Get sales chart data (AJAX)
     */
    public function getSalesChartData(Request $request)
    {
        try {
            $period = $request->get('period', 'month'); // day, week, month, year
            $dateRange = $this->getDateRange($request);

            $query = Order::selectRaw('
                    DATE(created_at) as date,
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_sales,
                    SUM(paid_amount) as total_paid
                ')
                ->when($dateRange['start'], fn($q) => $q->whereDate('created_at', '>=', $dateRange['start']))
                ->when($dateRange['end'], fn($q) => $q->whereDate('created_at', '<=', $dateRange['end']))
                ->groupBy('date')
                ->orderBy('date');

            if ($period === 'month') {
                $query->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as date')
                    ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")');
            } elseif ($period === 'year') {
                $query->selectRaw('YEAR(created_at) as date')
                    ->groupByRaw('YEAR(created_at)');
            }

            $salesData = $query->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $salesData->pluck('date')->toArray(),
                    'orders' => $salesData->pluck('total_orders')->toArray(),
                    'sales' => $salesData->pluck('total_sales')->toArray(),
                    'paid' => $salesData->pluck('total_paid')->toArray(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Sales Chart Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error loading sales data'], 500);
        }
    }

    /**
     * Get revenue chart data (AJAX)
     */
    public function getRevenueChartData(Request $request)
    {
        try {
            $period = $request->get('period', 'month');
            $dateRange = $this->getDateRange($request);

            // Income from vendor payments
            $incomeQuery = VendorAccount::where('type', 2)
                ->when($dateRange['start'], fn($q) => $q->whereDate('created_at', '>=', $dateRange['start']))
                ->when($dateRange['end'], fn($q) => $q->whereDate('created_at', '<=', $dateRange['end']));

            if ($period === 'month') {
                $incomeData = $incomeQuery->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as period, SUM(amount) as total')
                    ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
                    ->orderBy('period')
                    ->get();
            } elseif ($period === 'year') {
                $incomeData = $incomeQuery->selectRaw('YEAR(created_at) as period, SUM(amount) as total')
                    ->groupByRaw('YEAR(created_at)')
                    ->orderBy('period')
                    ->get();
            } else {
                $incomeData = $incomeQuery->selectRaw('DATE(created_at) as period, SUM(amount) as total')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $incomeData->pluck('period')->toArray(),
                    'income' => $incomeData->pluck('total')->toArray(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Revenue Chart Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error loading revenue data'], 500);
        }
    }

    /**
     * Get expenses chart data (AJAX)
     */
    public function getExpensesChartData(Request $request)
    {
        try {
            $period = $request->get('period', 'month');
            $dateRange = $this->getDateRange($request);

            $expenseQuery = ExpenseList::active()
                ->when($dateRange['start'], fn($q) => $q->whereDate('expense_date', '>=', $dateRange['start']))
                ->when($dateRange['end'], fn($q) => $q->whereDate('expense_date', '<=', $dateRange['end']));

            if ($period === 'month') {
                $expenseData = $expenseQuery->selectRaw('DATE_FORMAT(expense_date, "%Y-%m") as period, SUM(amount) as total')
                    ->groupByRaw('DATE_FORMAT(expense_date, "%Y-%m")')
                    ->orderBy('period')
                    ->get();
            } elseif ($period === 'year') {
                $expenseData = $expenseQuery->selectRaw('YEAR(expense_date) as period, SUM(amount) as total')
                    ->groupByRaw('YEAR(expense_date)')
                    ->orderBy('period')
                    ->get();
            } else {
                $expenseData = $expenseQuery->selectRaw('DATE(expense_date) as period, SUM(amount) as total')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
            }

            // Also get expense by category
            $expenseByCategory = ExpenseList::with('expenseHead')
                ->active()
                ->when($dateRange['start'], fn($q) => $q->whereDate('expense_date', '>=', $dateRange['start']))
                ->when($dateRange['end'], fn($q) => $q->whereDate('expense_date', '<=', $dateRange['end']))
                ->selectRaw('expense_head_id, SUM(amount) as total')
                ->groupBy('expense_head_id')
                ->get();

            // Get category labels and values
            $categoryLabels = [];
            $categoryValues = [];
            foreach ($expenseByCategory as $expense) {
                if ($expense->expenseHead) {
                    $categoryLabels[] = $expense->expenseHead->title;
                    $categoryValues[] = (float) $expense->total;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $expenseData->pluck('period')->toArray(),
                    'expenses' => $expenseData->pluck('total')->map(fn($val) => (float) $val)->toArray(),
                    'by_category' => [
                        'labels' => $categoryLabels,
                        'values' => $categoryValues,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Expenses Chart Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error loading expenses data'], 500);
        }
    }

    /**
     * Get profit chart data (AJAX)
     */
    public function getProfitChartData(Request $request)
    {
        try {
            $period = $request->get('period', 'month');
            $dateRange = $this->getDateRange($request);

            $profitData = Order::with('orderItems.orderItemStocks')
                ->when($dateRange['start'], fn($q) => $q->whereDate('created_at', '>=', $dateRange['start']))
                ->when($dateRange['end'], fn($q) => $q->whereDate('created_at', '<=', $dateRange['end']))
                ->get()
                ->groupBy(function ($order) use ($period) {
                    if ($period === 'month') {
                        return $order->created_at->format('Y-m');
                    } elseif ($period === 'year') {
                        return $order->created_at->format('Y');
                    } else {
                        return $order->created_at->format('Y-m-d');
                    }
                })
                ->map(function ($orders) {
                    return $orders->sum(function ($order) {
                        return $order->orderItems->sum(function ($item) {
                            return $item->orderItemStocks->sum(function ($stock) {
                                return ($stock->actual_profit ?? 0) - ($stock->discount_amount ?? 0);
                            });
                        });
                    });
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => array_keys($profitData->toArray()),
                    'profit' => array_values($profitData->toArray()),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Profit Chart Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error loading profit data'], 500);
        }
    }

    /**
     * Get products chart data (AJAX)
     */
    public function getProductsChartData(Request $request)
    {
        try {
            // Top selling products
            $topSellingProducts = OrderItem::with('product')
                ->selectRaw('product_id, SUM(quantity) as total_sold, SUM(total_price) as total_revenue')
                ->groupBy('product_id')
                ->orderByDesc('total_sold')
                ->limit(10)
                ->get();

            // Low stock products (available quantity)
            $lowStockProducts = Stock::with('product')
                ->selectRaw('product_id, SUM(quantity - sold_quantity - damage_quantity - stolen_quantity - froze_quantity) as total_quantity')
                ->groupBy('product_id')
                ->havingRaw('total_quantity < 50')
                ->orderBy('total_quantity')
                ->limit(10)
                ->get();

            // Stock by warehouse (available quantity with purchase price)
            $stockByWarehouse = Stock::with('warehouse')
                ->selectRaw('warehouse_id, SUM(quantity - sold_quantity - damage_quantity - stolen_quantity - froze_quantity) as total_quantity, SUM((quantity - sold_quantity - damage_quantity - stolen_quantity - froze_quantity) * purchase_price) as total_value')
                ->groupBy('warehouse_id')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'top_selling' => [
                        'labels' => $topSellingProducts->pluck('product.name')->toArray(),
                        'quantities' => $topSellingProducts->pluck('total_sold')->toArray(),
                        'revenue' => $topSellingProducts->pluck('total_revenue')->toArray(),
                    ],
                    'low_stock' => [
                        'labels' => $lowStockProducts->pluck('product.name')->toArray(),
                        'quantities' => $lowStockProducts->pluck('total_quantity')->toArray(),
                    ],
                    'by_warehouse' => [
                        'labels' => $stockByWarehouse->pluck('warehouse.name')->toArray(),
                        'quantities' => $stockByWarehouse->pluck('total_quantity')->toArray(),
                        'values' => $stockByWarehouse->pluck('total_value')->toArray(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Products Chart Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error loading products data'], 500);
        }
    }

    /**
     * Get recent orders (AJAX)
     */
    public function getRecentOrders(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);

            $recentOrders = Order::with(['vendor', 'orderStatus', 'admin'])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'invoice_id' => $order->invoice_id,
                        'vendor' => $order->vendor->shop_name ?? 'N/A',
                        'total_amount' => number_format($order->total_amount, 2),
                        'paid_amount' => number_format($order->paid_amount, 2),
                        'status' => $order->orderStatus->name ?? 'N/A',
                        'created_at' => $order->created_at->format('Y-m-d H:i'),
                        'created_by' => $order->admin->name ?? 'N/A',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $recentOrders
            ]);

        } catch (\Exception $e) {
            Log::error('Recent Orders Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error loading recent orders'], 500);
        }
    }

    /**
     * Get top products (AJAX)
     */
    public function getTopProducts(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $dateRange = $this->getDateRange($request);

            $topProducts = OrderItem::with('product')
                ->when($dateRange['start'], fn($q) => $q->whereHas('order', function ($query) use ($dateRange) {
                    $query->whereDate('created_at', '>=', $dateRange['start']);
                }))
                ->when($dateRange['end'], fn($q) => $q->whereHas('order', function ($query) use ($dateRange) {
                    $query->whereDate('created_at', '<=', $dateRange['end']);
                }))
                ->selectRaw('product_id, SUM(quantity) as total_sold, SUM(total_price) as total_revenue')
                ->groupBy('product_id')
                ->orderByDesc('total_sold')
                ->limit($limit)
                ->get()
                ->map(function ($item) {
                    return [
                        'product_name' => $item->product->name ?? 'N/A',
                        'total_sold' => $item->total_sold,
                        'total_revenue' => number_format($item->total_revenue, 2),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $topProducts
            ]);

        } catch (\Exception $e) {
            Log::error('Top Products Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error loading top products'], 500);
        }
    }

    /**
     * Get user orders chart data (AJAX) - Shows order amount by each SR/DSR user
     */
    public function getUserOrdersChartData(Request $request)
    {
        try {
            $period = $request->get('period', 'month');
            $dateRange = $this->getDateRange($request);

            // Get current user if DSR/SR, otherwise get all DSR/SR users
            $currentUser = Auth::guard('admin')->user();
            $userRoles = $currentUser->getRoleNames();
            $isRestrictedUser = $userRoles->contains('dsr') || $userRoles->contains('sr');

            $query = Order::with('placeBy')->where('order_status_id', '!=', 6) // Exclude cancelled orders
                ->when($dateRange['start'], fn($q) => $q->whereDate('created_at', '>=', $dateRange['start']))
                ->when($dateRange['end'], fn($q) => $q->whereDate('created_at', '<=', $dateRange['end']));

            // If restricted user, only show their own data
            if ($isRestrictedUser) {
                $query->where('place_by', $currentUser->id);
            }
            // Admin sees all users - no role restriction

            // Group by person and date based on period
            if ($period === 'month') {
                $ordersData = $query->selectRaw('place_by, DATE_FORMAT(created_at, "%Y-%m") as period, SUM(total_amount) as total_amount')
                    ->groupByRaw('place_by, DATE_FORMAT(created_at, "%Y-%m")')
                    ->orderBy('period')
                    ->get();
            } elseif ($period === 'year') {
                $ordersData = $query->selectRaw('place_by, YEAR(created_at) as period, SUM(total_amount) as total_amount')
                    ->groupByRaw('place_by, YEAR(created_at)')
                    ->orderBy('period')
                    ->get();
            } else {
                // Default: daily
                $ordersData = $query->selectRaw('place_by, DATE(created_at) as period, SUM(total_amount) as total_amount')
                    ->groupByRaw('place_by, DATE(created_at)')
                    ->orderBy('period')
                    ->get();
            }

            // Group by period to get all unique dates
            $grouped = $ordersData->groupBy('period');
            $labels = $grouped->keys()->toArray();

            // Get unique users
            $users = $ordersData->pluck('placeBy')->unique('id')->values();

            // Prepare series data for each user
            $series = [];
            foreach ($users as $user) {
                if ($user) {
                    $userData = [];
                    foreach ($labels as $label) {
                        $orderAmount = $grouped[$label]->where('place_by', $user->id)->sum('total_amount');
                        $userData[] = (float) $orderAmount;
                    }
                    $series[] = [
                        'name' => $user->name,
                        'data' => $userData
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'series' => $series
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('User Orders Chart Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error loading user orders data'], 500);
        }
    }

    /**
     * Get user collection chart data (AJAX) - Shows collection amount by each SR/DSR user
     */
    public function getUserCollectionChartData(Request $request)
    {
        try {
            $period = $request->get('period', 'month');
            $dateRange = $this->getDateRange($request);

            // Get current user if DSR/SR, otherwise get all DSR/SR users
            $currentUser = Auth::guard('admin')->user();
            $userRoles = $currentUser->getRoleNames();
            $isRestrictedUser = $userRoles->contains('dsr') || $userRoles->contains('sr');

            $query = VendorAccount::with('depositeBy')
                ->where('type', 2) // Type 2 = Credit (Collection)
                ->when($dateRange['start'], fn($q) => $q->whereDate('collection_date', '>=', $dateRange['start']))
                ->when($dateRange['end'], fn($q) => $q->whereDate('collection_date', '<=', $dateRange['end']));

            // If restricted user, only show their own data
            if ($isRestrictedUser) {
                $query->where('deposite_by', $currentUser->id);
            }
            // Admin sees all users - no role restriction

            // Group by person and date based on period
            if ($period === 'month') {
                $collectionData = $query->selectRaw('deposite_by, DATE_FORMAT(collection_date, "%Y-%m") as period, SUM(amount) as total_collection')
                    ->groupByRaw('deposite_by, DATE_FORMAT(collection_date, "%Y-%m")')
                    ->orderBy('period')
                    ->get();
            } elseif ($period === 'year') {
                $collectionData = $query->selectRaw('deposite_by, YEAR(collection_date) as period, SUM(amount) as total_collection')
                    ->groupByRaw('deposite_by, YEAR(collection_date)')
                    ->orderBy('period')
                    ->get();
            } else {
                // Default: daily
                $collectionData = $query->selectRaw('deposite_by, DATE(collection_date) as period, SUM(amount) as total_collection')
                    ->groupByRaw('deposite_by, DATE(collection_date)')
                    ->orderBy('period')
                    ->get();
            }

            // Group by period to get all unique dates
            $grouped = $collectionData->groupBy('period');
            $labels = $grouped->keys()->toArray();

            // Get unique users
            $users = $collectionData->pluck('depositeBy')->unique('id')->values();

            // Prepare series data for each user
            $series = [];
            foreach ($users as $user) {
                if ($user) {
                    $userData = [];
                    foreach ($labels as $label) {
                        $collection = $grouped[$label]->where('deposite_by', $user->id)->sum('total_collection');
                        $userData[] = (float) $collection;
                    }
                    $series[] = [
                        'name' => $user->name,
                        'data' => $userData
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'series' => $series
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('User Collection Chart Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error loading user collection data'], 500);
        }
    }

    /**
     * Helper method to get date range from request
     */
    private function getDateRange(Request $request)
    {
        $filter = $request->get('filter', 'all'); // all, today, week, month, year, custom

        $start = null;
        $end = null;

        switch ($filter) {
            case 'today':
                $start = now()->startOfDay();
                $end = now()->endOfDay();
                break;
            case 'week':
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
                break;
            case 'month':
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
                break;
            case 'year':
                $start = now()->startOfYear();
                $end = now()->endOfYear();
                break;
            case 'custom':
                $start = $request->get('start_date') ? \Carbon\Carbon::parse($request->get('start_date')) : null;
                $end = $request->get('end_date') ? \Carbon\Carbon::parse($request->get('end_date')) : null;
                break;
        }

        return ['start' => $start, 'end' => $end];
    }


    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('adminLogin');
    }


    public function otpLoad(Request $request)
    {
        $uuID = session('uuid') ?? $request->uuid;
        $admin = Admin::find($uuID);

        if (!$admin) {
            return back()->with([
                'message' => 'User Not Found',
                'alert-type' => 'error'
            ]);
        }

        $randCode = rand(100000,999999);
        $toster = array(
            'message' => 'User Found',
            'alert-type' => 'success'
        );
        $status = storeOtp($admin, $randCode);
        $name = $admin->name;
        $messageContent = "Your Reset Code is : {$randCode}";

        // Email Code
        if($admin->email != null && $status == true)
        {
            Mail::to($admin->email)->queue(new ForgetPassMail($name,$messageContent));
        }
        else
        {
            return back()->with([
                'message' => 'Error in otp sending',
                'alert-type' => 'error'
            ]);
        }

        return view('auth.admin.otp', compact('admin'))->with($toster);

    }

    public function validateOtp(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'otp' => 'required|array|size:6',
            'otp.*' => 'required|digits:1',
        ]);



        if ($validator->fails()) {
            $toster = array(
                'message' => 'Wrong OTP',
                'alert-type' => 'error'
            );
            return redirect()->route('loadForgetMyPass')->with( $toster);
        }

        $otp = preg_replace('/\D/', '', implode('', $request->input('otp')));


        $admin = Admin::find($request->uuid);

        // if ($admin->otp == $request->otp && $admin->otp_validate_time > now())
        if ($admin?->otp == $otp)
        {
            return view('auth.admin.confirmpass', compact('admin'));
        }
        else
        {
            $toster = array(
                'message' => 'Wrong OTP',
                'alert-type' => 'error'
            );

            return back()->with( $toster);
        }
    }

    public function updatePassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'password_confirmation' => 'required|same:password',
        ],
        [
            'password.required' => 'The Password is required',
            'password_confirmation.required' => 'The Confirm Password is required',
            'password_confirmation.same' => 'The Confirm Password and Password must match',
        ]
    );

        if ($validator->fails()) {
            $toster = array(
                'message' => $validator->errors()->first(),
                'alert-type' => 'error'
            );
            return redirect()->route('adminLogin')->with( $toster);
        }


        $admin = Admin::find($request->uuid);
        $admin->password = Hash::make($request->password);
        $admin->save();

        $toster = array(
            'message' => 'Password Updated',
            'alert-type' => 'success'
        );

        return redirect()->route('adminLogin')->with($toster);
    }

    /**
     * Display the profile page
     */
    public function profile()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.profile', compact('admin'));
    }

    /**
     * Update admin profile information (name, phone, profile picture)
     */
    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'profile_picture' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:10048',
        ], [
            'name.required' => 'The name field is required',
            'profile_picture.image' => 'The file must be an image',
            'profile_picture.mimes' => 'Profile picture must be a file of type: jpeg, jpg, png, gif, webp',
            'profile_picture.max' => 'Profile picture may not be greater than 10MB',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with([
                'message' => $validator->errors()->first(),
                'alert-type' => 'error'
            ]);
        }

        try {
            $admin->name = $request->name;

            if ($request->filled('phone')) {
                $admin->phone = $request->phone;
            }

            $admin->save();

            // Handle profile picture upload using Spatie Media Library
            if ($request->hasFile('profile_picture')) {
                // Clear old profile picture
                $admin->clearMediaCollection('profile_picture');

                // Add new profile picture
                $admin->addMediaFromRequest('profile_picture')
                    ->toMediaCollection('profile_picture');
            }

            return back()->with([
                'message' => 'Profile updated successfully',
                'alert-type' => 'success'
            ]);

        } catch (\Exception $e) {
            Log::error('Profile Update Error: ' . $e->getMessage());
            return back()->with([
                'message' => 'Error updating profile: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * Update admin password
     */
    public function updateProfilePassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:6|different:old_password',
            'new_password_confirmation' => 'required|same:new_password',
        ], [
            'old_password.required' => 'The current password is required',
            'new_password.required' => 'The new password is required',
            'new_password.min' => 'The new password must be at least 6 characters',
            'new_password.different' => 'The new password must be different from the current password',
            'new_password_confirmation.required' => 'The confirm password is required',
            'new_password_confirmation.same' => 'The confirm password and new password must match',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with([
                'message' => $validator->errors()->first(),
                'alert-type' => 'error'
            ]);
        }

        try {
            // Check if old password matches
            if (!Hash::check($request->old_password, $admin->password)) {
                return back()->with([
                    'message' => 'The current password is incorrect',
                    'alert-type' => 'error'
                ]);
            }

            $admin->password = Hash::make($request->new_password);
            $admin->save();

            return back()->with([
                'message' => 'Password updated successfully',
                'alert-type' => 'success'
            ]);

        } catch (\Exception $e) {
            Log::error('Password Update Error: ' . $e->getMessage());
            return back()->with([
                'message' => 'Error updating password: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

}
