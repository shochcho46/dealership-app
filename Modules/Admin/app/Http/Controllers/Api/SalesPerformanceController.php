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
            // Validate date inputs and optional admin_id filter
            $validated = $request->validate([
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'admin_id' => 'nullable|integer|exists:admins,id',
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

            // Get ALL active admin users (always needed for meta calculations)
            $allUsers = Admin::with(['roles', 'media'])
                ->where('status', 1)
                ->get();

            if ($allUsers->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No active admin users found',
                    'data' => [],
                    'meta' => [
                        'date_from' => $dateFrom->format('Y-m-d'),
                        'date_to' => $dateTo->format('Y-m-d'),
                    ]
                ], 200);
            }

            // Determine which users to return in data section
            if ($request->filled('admin_id')) {
                // Filter to specific admin for data response
                $usersForData = $allUsers->where('id', $request->admin_id);
                
                // If admin_id was provided but no users found, return specific error
                if ($usersForData->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Admin not found or inactive'
                    ], 422);
                }
            } else {
                // Return all users in data response
                $usersForData = $allUsers;
            }

            // Calculate metrics for filtered users (for data section)
            $performanceData = $usersForData->map(function ($user) use ($dateFrom, $dateTo) {
                return $this->calculateUserPerformance($user, $dateFrom, $dateTo);
            });

            // Calculate system-wide totals from ALL users (for meta section)
            $allUsersPerformance = $allUsers->map(function ($user) use ($dateFrom, $dateTo) {
                return $this->calculateUserPerformance($user, $dateFrom, $dateTo);
            });

            $totalOrdersAmount = $allUsersPerformance->sum('sales.amount');
            $totalCollectionsCurrentPeriod = $allUsersPerformance->sum('individual_collections.from_current_period_orders');
            $totalCollectionsPreviousPeriod = $allUsersPerformance->sum('individual_collections.from_previous_period_orders');

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
                    'total_users' => $allUsers->count(),
                    'filtered_users' => $usersForData->count(),
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
        // PART 3: TARGET METRICS (for any user with sales target)
        // ========================================

        $targetMetrics = null;
        $targetAmount = $user->sales_target ?? 0;

        if ($targetAmount > 0) {
            $completionPercentage = ($salesAmount / $targetAmount) * 100;
            $amountRemaining = max(0, $targetAmount - $salesAmount);

            $targetMetrics = [
                'completion_percentage' => round($completionPercentage, 2),
                'amount_remaining' => round($amountRemaining, 2),
                'status' => $completionPercentage >= 100 ? 'Achieved' : 'In Progress',
            ];
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
