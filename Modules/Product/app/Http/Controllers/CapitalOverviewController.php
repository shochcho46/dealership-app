<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Models\InvestmentDetail;
use Modules\Product\Models\Asset;
use Modules\Product\Models\Stock;
use Modules\Product\Models\Order;
use Modules\Product\Models\OrderItem;
use Modules\Product\Models\BankAccountDetail;
use Modules\Product\Models\DamageReturnLost;

class CapitalOverviewController extends Controller
{
    /**
     * Display the capital overview.
     */
    public function index()
    {
        // Total Investment (sum of all investment_details amount)
        $totalInvestment = InvestmentDetail::sum('amount');

        // Total Assets where type = 1 (Investment type)
        $totalAssets = Asset::where('type', 1)->sum('price');

        // Available Stock Value (purchase_price * quantity)
        $availableStockValue = Stock::sum(DB::raw('purchase_price * quantity'));

        // Get all active orders (not cancelled)
        $activeOrders = Order::with('orderItems')
            ->whereHas('orderStatus', function ($q) {
                $q->where('name', '!=', 'Cancelled');
            })
            ->get();

        // Calculate Total Due Amount using purchase_price
        $totalDue = 0;
        $totalActualDuePrice = 0;

        foreach ($activeOrders as $order) {
            // Due amount = total_amount - paid_amount (for actual sale price)
            $orderDue = $order->total_amount - $order->paid_amount;
            $totalActualDuePrice += $orderDue;

            // Calculate due based on purchase price for capital calculation
            $orderPurchaseCost = $order->orderItems->sum(function ($item) {
                return $item->purchase_price * $item->quantity;
            });

            // Proportional due amount based on purchase price
            if ($order->total_amount > 0) {
                $dueRatio = ($order->total_amount - $order->paid_amount) / $order->total_amount;
                $totalDue += $orderPurchaseCost * $dueRatio;
            }
        }

        // Bank Balance (Credit - Debit)
        $bankCredit = BankAccountDetail::where('type', 1)->sum('amount');
        $bankDebit = BankAccountDetail::where('type', 2)->sum('amount');
        $bankBalance = $bankCredit - $bankDebit;

        // Damage and Lost amounts (using status field: 1=damage, 2=return, 3=lost)
        $totalDamage = DamageReturnLost::where('status', 1)
            ->sum(DB::raw('quantity * purchase_price'));

        $totalLost = DamageReturnLost::where('status', 3)
            ->sum(DB::raw('quantity * purchase_price'));

        // Capital Calculation
        // Capital Used = Assets (Type 1) + Stock Value + Due Amount (Purchase Price) + Damage + Lost
        $capitalUsed = $totalAssets + $availableStockValue + $totalDue + $totalDamage + $totalLost;

        // Available Capital = Total Investment + Bank Balance - Capital Used
        $availableCapital = $totalInvestment + $bankBalance - $capitalUsed;

        // Capital Status
        $capitalStatus = $availableCapital >= 0 ? 'balanced' : 'deficit';
        $capitalDifference = abs($availableCapital);

        return view('product::capital-overview.index', compact(
            'totalInvestment',
            'totalAssets',
            'availableStockValue',
            'totalDue',
            'totalActualDuePrice',
            'bankCredit',
            'bankDebit',
            'bankBalance',
            'totalDamage',
            'totalLost',
            'capitalUsed',
            'availableCapital',
            'capitalStatus',
            'capitalDifference'
        ));

    }
}



