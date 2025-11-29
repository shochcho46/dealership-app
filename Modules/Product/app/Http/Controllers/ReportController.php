<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Product;
use Modules\Product\Models\Stock;
use Modules\Product\Models\Order;
use Modules\Product\Models\OrderItem;
use Modules\Product\Models\OrderItemStock;
use Modules\Product\Models\Vendor;
use Modules\Product\Models\VendorAccount;
use Modules\Product\Models\PaymentMethod;
use Modules\Product\Models\DamageReturnLost;
use Modules\Product\Models\ExpenseList;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Stock Overview Report
     */
    public function stockOverview(Request $request)
    {
        $productName = Product::orderBy('name')->get();
        $query = Product::with(['stocks']);

        if ($request->filled('product_id')) {
            $query->where('id', $request->product_id);
        }

        $products = $query->get()->map(function ($product) {
            $totalPurchaseQty = 0;
            $totalPurchaseAmount = 0;
            $totalSoldQty = 0;
            $totalSoldAmount = 0;
            $availableQty = 0;
            $availableAmount = 0;
            $damageLostQty = 0;

            foreach ($product->stocks as $stock) {

                // Total Purchase
                $totalPurchaseQty += $stock->quantity;
                $totalPurchaseAmount += ($stock->quantity * $stock->purchase_price);

                // Total Sold
                $totalSoldQty += $stock->sold_quantity;
                $totalSoldAmount += ($stock->sold_quantity * $stock->sell_price);

                // Total Damage + Lost
                $damageLostQty += $stock->damage_quantity + $stock->stolen_quantity;

                // Available
                $available = $stock->quantity
                    - $stock->sold_quantity
                    - $stock->damage_quantity
                    - $stock->stolen_quantity
                    - $stock->froze_quantity;

                $availableQty += $available;
                $availableAmount += ($available * $stock->purchase_price);
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->product_image_thumb_url,
                'total_purchase_qty' => $totalPurchaseQty,
                'total_purchase_amount' => $totalPurchaseAmount,
                'total_sold_qty' => $totalSoldQty,
                'total_sold_amount' => $totalSoldAmount,
                'available_qty' => $availableQty,
                'available_amount' => $availableAmount,
                'total_damage_lost_qty' => $damageLostQty,
            ];
        });

        return view('product::reports.stock-overview', compact('products', 'productName'));
    }

    /**
     * Order Report with filters
     */
    public function orderReport(Request $request)
    {
        $limit = $request->limit ?? 50;
        $query = OrderItem::with(['order.vendor', 'order.placeBy', 'product', 'orderItemStocks.stock']);

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            });
        }

        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->where('vendor_id', $request->vendor_id);
            });
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by place_by
        if ($request->filled('place_by')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->where('place_by', $request->place_by);
            });
        }

        $fullQuery = clone $query;

        $orderItems = $query->orderBy('id', 'desc')->paginate($limit);


        $totalQuantity = $fullQuery->get()->sum('quantity') - $fullQuery->get()->sum('return_quantity');
        $totalPurchase = $fullQuery->get()->sum('total_purchase');
        $totalSellPrice = $fullQuery->get()->sum('total_sell');
        $totalDiscount = $fullQuery->get()->sum('discount_price');
        $totalProfit = $fullQuery->get()->sum('item_total_profit');


        $currentQuantityPage = $orderItems->getCollection()->sum('quantity') - $orderItems->getCollection()->sum('return_quantity');
        $currentPurchasePage = $orderItems->getCollection()->sum('total_purchase');
        $currentSellPricePage = $orderItems->getCollection()->sum('total_sell');
        $currentDiscountPage = $orderItems->getCollection()->sum('discount_price');
        $currentProfitPage = $orderItems->getCollection()->sum('item_total_profit');

        // Get filter data
        $vendors = Vendor::orderBy('shop_name')->get();
        $products = Product::orderBy('name')->get();
        $admins = Admin::role(['admin', 'subadmin', 'dsr', 'sr'])->orderBy('name')->get();

        return view('product::reports.order-report', compact('orderItems', 'vendors', 'products', 'admins', 'totalQuantity', 'totalPurchase', 'totalSellPrice', 'totalDiscount', 'totalProfit', 'currentQuantityPage', 'currentPurchasePage', 'currentSellPricePage', 'currentDiscountPage', 'currentProfitPage'));
    }

    /**
     * Profitable Product Report
     */
    public function profitableProduct(Request $request)
    {
        $query = Product::with(['orderItems.orderItemStocks']);

        // Get date range
        $dateFrom = $request->filled('date_from') ? $request->date_from : null;
        $dateTo = $request->filled('date_to') ? $request->date_to : null;

        $products = $query->get()->map(function ($product) use ($dateFrom, $dateTo) {
            $totalSoldQty = 0;
            $totalProfit = 0;
            $totalRevenue = 0;
            $totalCost = 0;

            foreach ($product->orderItems as $orderItem) {
                // Filter by date if provided
                if ($dateFrom && $orderItem->order->created_at < $dateFrom) {
                    continue;
                }
                if ($dateTo && $orderItem->order->created_at > $dateTo . ' 23:59:59') {
                    continue;
                }

                // Only count sold items (shipped or delivered)
                if (in_array($orderItem->order->order_status_id, [4, 5])) {
                    $totalSoldQty += ($orderItem->quantity - $orderItem->return_quantity);
                    $totalRevenue += $orderItem->sell_price * ($orderItem->quantity - $orderItem->return_quantity);

                    foreach ($orderItem->orderItemStocks as $orderItemStock) {
                        $totalCost += ($orderItemStock->quantity - $orderItemStock->return_quantity) * $orderItemStock->purchase_price;
                        $totalProfit += ($orderItemStock->actual_profit - $orderItemStock->discount_amount);
                    }
                }
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->product_image_thumb_url,
                'total_sold_qty' => $totalSoldQty,
                'total_revenue' => $totalRevenue,
                'total_cost' => $totalCost,
                'total_profit' => $totalProfit,
                'profit_margin' => $totalRevenue > 0 ? (($totalProfit / $totalRevenue) * 100) : 0,
            ];
        })->filter(function ($item) {
            return $item['total_sold_qty'] > 0; // Only show products that have been sold
        })->sortByDesc('total_profit')->values();

        return view('product::reports.profitable-product', compact('products'));
    }

    /**
     * Collection Report - VendorAccount type 2 (Credit)
     */
    public function collectionReport(Request $request)
    {
        $limit = $request->limit ?? 50;
        $query = VendorAccount::with(['vendor', 'paymentMethod', 'depositeBy'])->where('type', 2);

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('collection_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('collection_date', '<=', $request->date_to);
        }

        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter by payment method
        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        // Filter by deposite_by (admin)
        if ($request->filled('deposite_by')) {
            $query->where('deposite_by', $request->deposite_by);
        }

        // Clone query for total calculation before pagination
        $fullQuery = clone $query;

        // Paginate results
        $accounts = $query->orderBy('collection_date', 'desc')->paginate($limit)->withQueryString();

        // Calculate totals
        $filteredTotal = $fullQuery->sum('amount');
        $pageTotal = $accounts->getCollection()->sum('amount');

        // Get filter data
        $vendors = Vendor::orderBy('shop_name')->get();
        $paymentMethods = PaymentMethod::orderBy('id')->get();
        $admins = Admin::role(['admin', 'subadmin', 'dsr', 'sr'])->orderBy('name')->get();

        return view('product::reports.collection-report', compact('accounts', 'vendors', 'paymentMethods', 'admins', 'filteredTotal', 'pageTotal'));
    }

    /**
     * Sell Summary Report
     */
    public function sellSummary(Request $request)
    {
        // Set default dates: first day of current month to today
        $dateFrom = $request->filled('date_from') ? $request->date_from : Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->filled('date_to') ? $request->date_to : Carbon::now()->format('Y-m-d');

        $query = Order::with(['vendor', 'orderItems.product', 'orderItems.orderItemStocks']);

        // Filter by date range
        $query->whereDate('created_at', '>=', $dateFrom);
        $query->whereDate('created_at', '<=', $dateTo);

        // Filter by product
        if ($request->filled('product_id')) {
            $query->whereHas('orderItems', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        $orders = $query->active()->get();

        // Calculate totals from orders
        $totalPurchasePrice = 0;
        $totalSellPrice = 0;
        $totalCollected = 0;
        $tentativeProfit = 0;
        $actualProfit = 0;
        $totalDue = 0;
        $totalDiscount = 0;

        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                // Filter by product if specified
                if ($request->filled('product_id') && $item->product_id != $request->product_id) {
                    continue;
                }

                $totalPurchasePrice += $item?->total_purchase;
                $totalSellPrice += $item?->total_sell;
                $tentativeProfit += ($item?->total_sell - $item?->total_purchase);
                $actualProfit += $item?->item_total_profit;
                $totalDiscount += $item?->discount_price ?? 0;
            }

            $totalCollected += $order?->paid_amount ?? 0;
        }



        // Calculate Damage/Return/Lost costs
        $damageReturnLostQuery = DamageReturnLost::with('product');
        $damageReturnLostQuery->whereDate('created_at', '>=', $dateFrom);
        $damageReturnLostQuery->whereDate('created_at', '<=', $dateTo);

        if ($request->filled('product_id')) {
            $damageReturnLostQuery->where('product_id', $request->product_id);
        }

        $damageReturnLostRecords = $damageReturnLostQuery->get();
        $totalDamageReturnLostCost = $damageReturnLostRecords->sum('total_price');
        $totalDamageCost = $damageReturnLostRecords->where('status',1)->sum('total_price');
        $totalReturnCost = $damageReturnLostRecords->where('status',2)->sum('total_price');

        // Calculate Expenses
        $expenseQuery = ExpenseList::with('expenseHead');
        $expenseQuery->whereDate('expense_date', '>=', $dateFrom);
        $expenseQuery->whereDate('expense_date', '<=', $dateTo);
        $expenseQuery->where('status', 1); // Active expenses only

        $expenses = $expenseQuery->get();
        $totalExpenses = $expenses->sum('amount');

        // Recalculate actual profit after deducting damage/return/lost and expenses
        $actualProfitAfterDeductions = $actualProfit - $totalDamageCost - $totalExpenses;
        $totalDue = $totalSellPrice - ($totalCollected + $totalDamageCost + $totalDiscount);
        // Get filter data
        $products = Product::orderBy('name')->get();

        return view('product::reports.sell-summary', compact(
            'totalPurchasePrice',
            'totalSellPrice',
            'totalCollected',
            'tentativeProfit',
            'actualProfit',
            'totalDue',
            'products',
            'totalExpenses',
            'actualProfitAfterDeductions',
            'dateFrom',
            'dateTo',
            'totalDiscount',
            'totalDamageCost',
            'totalReturnCost',
            'totalDamageReturnLostCost',
        ));
    }

    /**
     * Due Orders List - Full page view
     */
    public function dueOrdersList(Request $request)
    {
        // Set default dates if not provided
        $dateFrom = $request->filled('date_from') ? $request->date_from : Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->filled('date_to') ? $request->date_to : Carbon::now()->format('Y-m-d');

        $limit = $request->limit ?? 50;
        $query = Order::with(['vendor', 'orderStatus', 'placeBy'])
            ->where('payment_status', '!=', 2); // Not fully paid

        // Filter by date range
        $query->whereDate('created_at', '>=', $dateFrom);
        $query->whereDate('created_at', '<=', $dateTo);

        // Filter by product
        if ($request->filled('product_id')) {
            $query->whereHas('orderItems', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        // Clone query for totals before pagination
        $fullQuery = clone $query;

        // Get paginated results
        $dueOrders = $query->active()->orderBy('created_at', 'desc')->paginate($limit)->withQueryString();

        // Calculate totals
        $allOrders = $fullQuery->active()->get();
        $totalAmount = $allOrders->sum('total_amount');
        $totalPaid = $allOrders->sum('paid_amount');
        $totalDue = $totalAmount - $totalPaid;

        // Page totals
        $pageTotal = $dueOrders->sum('total_amount');
        $pagePaid = $dueOrders->sum('paid_amount');
        $pageDue = $pageTotal - $pagePaid;

        // Get products for filter
        $products = Product::orderBy('name')->get();

        return view('product::reports.due-orders-list', compact(
            'dueOrders',
            'products',
            'dateFrom',
            'dateTo',
            'totalAmount',
            'totalPaid',
            'totalDue',
            'pageTotal',
            'pagePaid',
            'pageDue'
        ));
    }
}
