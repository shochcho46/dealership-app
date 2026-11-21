<?php

namespace Modules\Product\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Models\Order;

/**
 * SR commission per order.
 *
 *   base       = product's commission price (products.commission_amount) if set, else the stock's sell price
 *   price_diff = Σ (sold price − base) × sold qty                         (+ over price / − under price)
 *   discount   = Σ discount given on the lines (for the sold qty)
 *   commission = price_diff − discount                                   (+ paid to the SR / − SR pays)
 *
 * Sold qty = quantity − returned quantity. Calculated per stock allocation (order_item_stocks),
 * so an item split across stocks with different set prices is still exact.
 */
class CommissionReport
{
    public const CANCELLED_STATUS = 6;

    /** Per-order commission figures (subquery). */
    public static function perOrder()
    {
        return DB::table('order_item_stocks as s')
            ->join('order_items as i', 'i.id', '=', 's.orderitem_id')
            ->join('stocks as st', 'st.id', '=', 's.stock_id')
            ->leftJoin('products as p', 'p.id', '=', 'i.product_id')
            ->groupBy('i.order_id')
            ->selectRaw('i.order_id,
                ROUND(SUM((s.sell_price - COALESCE(p.commission_amount, st.sell_price)) * (s.quantity - s.return_quantity)), 2) AS price_diff,
                ROUND(SUM(CASE WHEN s.quantity > 0 THEN s.discount_amount * (s.quantity - s.return_quantity) / s.quantity ELSE 0 END), 2) AS discount');
    }

    /** Filtered orders with price_diff, discount and commission columns. */
    public static function query(Request $request): Builder
    {
        $query = Order::query()
            ->joinSub(self::perOrder(), 'c', 'c.order_id', '=', 'orders.id')
            ->select('orders.*', 'c.price_diff', 'c.discount', DB::raw('(c.price_diff - c.discount) AS commission'))
            ->with(['vendor:id,shop_name,shop_name_bn', 'placeBy:id,name', 'orderStatus:id,name']);

        if ($request->filled('date_from')) {
            $query->whereDate('orders.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('orders.created_at', '<=', $request->date_to);
        }
        if ($request->filled('place_by')) {
            $query->where('orders.place_by', (int) $request->place_by);
        }
        if ($request->filled('vendor_id')) {
            $query->where('orders.vendor_id', (int) $request->vendor_id);
        }

        // Status: default = everything except cancelled; "all" = include cancelled too.
        $status = $request->input('status');
        if ($status === 'all') {
            // no condition
        } elseif ($status !== null && $status !== '') {
            $query->where('orders.order_status_id', (int) $status);
        } else {
            $query->where('orders.order_status_id', '!=', self::CANCELLED_STATUS);
        }

        if ($request->input('result') === 'plus') {
            $query->whereRaw('(c.price_diff - c.discount) > 0');
        } elseif ($request->input('result') === 'minus') {
            $query->whereRaw('(c.price_diff - c.discount) < 0');
        }

        return $query;
    }

    /** Grand totals over every order matching the filters. */
    public static function totals(Request $request): array
    {
        $row = DB::query()->fromSub(self::query($request)->setEagerLoads([]), 'x')
            ->selectRaw('COUNT(*) AS orders,
                COALESCE(SUM(total_amount), 0) AS total_amount,
                COALESCE(SUM(price_diff), 0) AS price_diff,
                COALESCE(SUM(discount), 0) AS discount,
                COALESCE(SUM(CASE WHEN commission > 0 THEN commission ELSE 0 END), 0) AS plus,
                COALESCE(SUM(CASE WHEN commission < 0 THEN commission ELSE 0 END), 0) AS minus,
                COALESCE(SUM(commission), 0) AS net,
                SUM(CASE WHEN commission > 0 THEN 1 ELSE 0 END) AS plus_orders,
                SUM(CASE WHEN commission < 0 THEN 1 ELSE 0 END) AS minus_orders')
            ->first();

        return array_map(fn ($v) => (float) $v, (array) $row);
    }
}
