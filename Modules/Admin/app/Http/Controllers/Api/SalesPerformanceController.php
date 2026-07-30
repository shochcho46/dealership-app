<?php

namespace Modules\Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Product\Models\Order;
use Modules\Product\Models\VendorAccount;
use Modules\Product\Models\DsrCollection;
use Modules\Admin\Http\Resources\SalesPerformanceResource;

class SalesPerformanceController extends Controller
{
    /**
     * Get sales performance metrics for SR and DSR users
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Validate date inputs
            $validated = $request->validate([
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
            ]);

            // Set date range (default to current month)
            $dateFrom = $request->filled('date_from')
                ? Carbon::parse($request->date_from)->startOfDay()
                : Carbon::now()->startOfMonth();

            $dateTo = $request->filled('date_to')
                ? Carbon::parse($request->date_to)->endOfDay()
                : Carbon::now()->endOfMonth();

            // Validate date range span (max 1 year)
            if ($dateFrom->diffInDays($dateTo) > 365) {
                return response()->json([
                    'success' => false,
                    'message' => 'Date range cannot exceed 1 year'
                ], 422);
            }

            // Get all SR and DSR users (role IDs 4 and 5)
            // Using whereHas to explicitly query roles with admin guard
            $users = Admin::with(['roles', 'media'])
                ->whereHas('roles', function($query) {
                    $query->whereIn('name', ['sr', 'dsr'])
                          ->where('guard_name', 'admin');
                })
                ->where('status', 1)
                ->get();

            if ($users->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No SR or DSR users found',
                    'data' => [],
                    'meta' => [
                        'date_from' => $dateFrom->format('Y-m-d'),
                        'date_to' => $dateTo->format('Y-m-d'),
                    ]
                ], 200);
            }

            // Calculate metrics for each user
            $performanceData = $users->map(function ($user) use ($dateFrom, $dateTo) {
                return $this->calculateUserPerformance($user, $dateFrom, $dateTo);
            });

            // Calculate system-wide totals
            $totalOrdersAmount = $performanceData->sum('sales.amount');
            $totalCollectionsCurrentPeriod = $performanceData->sum('individual_collections.from_current_period_orders');
            $totalCollectionsPreviousPeriod = $performanceData->sum('individual_collections.from_previous_period_orders');

            // Calculate total due and percentages
            $totalDueAmount = $totalOrdersAmount - $totalCollectionsCurrentPeriod;
            $collectionPercentageCurrentPeriod = $totalOrdersAmount > 0
                ? ($totalCollectionsCurrentPeriod / $totalOrdersAmount) * 100
                : 0;
            $duePercentage = $totalOrdersAmount > 0
                ? ($totalDueAmount / $totalOrdersAmount) * 100
                : 0;

            return response()->json([
                'success' => true,
                'data' => SalesPerformanceResource::collection($performanceData),
                'meta' => [
                    'date_from' => $dateFrom->format('Y-m-d'),
                    'date_to' => $dateTo->format('Y-m-d'),
                    'total_users' => $users->count(),
                    'total_orders_amount' => number_format($totalOrdersAmount, 2, '.', ''),
                    'total_collections_current_period' => number_format($totalCollectionsCurrentPeriod, 2, '.', ''),
                    'total_collections_previous_period' => number_format($totalCollectionsPreviousPeriod, 2, '.', ''),
                    'total_due_amount' => number_format($totalDueAmount, 2, '.', ''),
                    'collection_percentage_current_period' => number_format($collectionPercentageCurrentPeriod, 2, '.', ''),
                    'due_percentage' => number_format($duePercentage, 2, '.', ''),
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching sales performance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate performance metrics for a single user
     *
     * @param Admin $user
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @return array
     */
    private function calculateUserPerformance(Admin $user, Carbon $dateFrom, Carbon $dateTo)
    {
        // Detect user role
        $userRole = $user->roles->first();
        $isDsr = $userRole && $userRole->name === 'dsr';

        // ========================================
        // PART 1: SALES (Orders MADE by this user)
        // ========================================

        // Get current period orders placed by this user
        $currentPeriodOrders = Order::where('place_by', $user->id)
            ->where('order_status_id', '!=', 6) // Exclude cancelled
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        $currentPeriodOrderIds = $currentPeriodOrders->pluck('id')->toArray();

        // Sales amount and count
        $salesAmount = $currentPeriodOrders->sum('total_amount');
        $orderCount = $currentPeriodOrders->count();

        // Total collections received against THEIR orders (by ANYONE)
        $collectionsAgainstTheirOrders = VendorAccount::where('type', 2)
            ->whereIn('order_id', $currentPeriodOrderIds)
            ->sum('amount');

        // Due amount from their orders
        $salesDueAmount = $salesAmount - $collectionsAgainstTheirOrders;

        // Collection percentage against their orders
        $salesCollectionPercentage = $salesAmount > 0
            ? ($collectionsAgainstTheirOrders / $salesAmount) * 100
            : 0;

        // ========================================
        // PART 2: INDIVIDUAL COLLECTIONS (What THEY collected)
        // ========================================

        // Collections THEY made from current period orders (during the date range)
        if ($isDsr) {
            // DSR: Collections from ALL orders created in current period
            $individualCurrentPeriodCollection = VendorAccount::where('deposite_by', $user->id)
                ->where('type', 2)
                ->whereHas('order', function($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo])
                          ->where('order_status_id', '!=', 6);
                })
                ->whereBetween('collection_date', [$dateFrom, $dateTo])
                ->sum('amount');
        } else {
            // SR: Collections from own orders only (during the date range)
            $individualCurrentPeriodCollection = VendorAccount::where('deposite_by', $user->id)
                ->where('type', 2)
                ->whereIn('order_id', $currentPeriodOrderIds)
                ->whereBetween('collection_date', [$dateFrom, $dateTo])
                ->sum('amount');
        }

        // Collections THEY made from previous period orders
        if ($isDsr) {
            // DSR: Collections from ALL orders created before period
            $individualPreviousPeriodCollection = VendorAccount::where('deposite_by', $user->id)
                ->where('type', 2)
                ->whereHas('order', function($query) use ($dateFrom) {
                    $query->where('created_at', '<', $dateFrom)
                          ->where('order_status_id', '!=', 6);
                })
                ->whereBetween('collection_date', [$dateFrom, $dateTo])
                ->sum('amount');
        } else {
            // SR: Collections from own old orders
            $individualPreviousPeriodCollection = VendorAccount::where('deposite_by', $user->id)
                ->where('type', 2)
                ->whereHas('order', function($query) use ($user, $dateFrom) {
                    $query->where('place_by', $user->id)
                          ->where('created_at', '<', $dateFrom)
                          ->where('order_status_id', '!=', 6);
                })
                ->whereBetween('collection_date', [$dateFrom, $dateTo])
                ->sum('amount');
        }

        // DSR collections THEY made (independent, separate entity)
        $individualDsrCollections = DsrCollection::where('deposite_by', $user->id)
            ->whereBetween('collection_date', [$dateFrom, $dateTo])
            ->sum('amount');

        // ========================================
        // PART 3: TARGET METRICS (for SR only)
        // ========================================

        $targetMetrics = null;
        if ($userRole && strtolower($userRole->name) === 'sr') {
            $targetAmount = $user->sales_target ?? 0;

            if ($targetAmount > 0) {
                $completionPercentage = ($salesAmount / $targetAmount) * 100;
                $amountRemaining = max(0, $targetAmount - $salesAmount);

                $targetMetrics = [
                    'completion_percentage' => round($completionPercentage, 2),
                    'amount_remaining' => round($amountRemaining, 2),
                    'status' => $completionPercentage >= 100 ? 'Achieved' : 'In Progress',
                ];
            } else {
                // No target set
                $targetMetrics = [
                    'completion_percentage' => 0,
                    'amount_remaining' => 0,
                    'status' => 'No Target Set',
                ];
            }
        }

        return [
            'user' => $user,
            'sales' => [
                'amount' => $salesAmount,
                'order_count' => $orderCount,
                'collections_received' => $collectionsAgainstTheirOrders,
                'due_amount' => $salesDueAmount,
                'collection_percentage' => round($salesCollectionPercentage, 2),
            ],
            'individual_collections' => [
                'from_current_period_orders' => $individualCurrentPeriodCollection,
                'from_previous_period_orders' => $individualPreviousPeriodCollection,
            ],
            'dsr_collections' => $individualDsrCollections,
            'target_metrics' => $targetMetrics,
        ];
    }
}
