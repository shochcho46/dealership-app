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
use App\Models\Admin;
use Illuminate\Support\Facades\DB;

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
}
