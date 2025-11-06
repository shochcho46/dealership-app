<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Product\Models\DamageReturnLost;
use Modules\Product\Models\Order;
use Modules\Product\Models\OrderItem;
use Modules\Product\Models\OrderItemStock;
use Modules\Product\Models\Product;
use Modules\Product\Models\Stock;
use Modules\Product\Models\Vendor;
use Modules\Product\Models\VendorAccount;

class DamageReturnLostController extends Controller
{
    /**
     * Display listing of damage/return/lost items
     */
    public function index(Request $request)
    {
        $query = DamageReturnLost::with(['order', 'orderItem.product', 'stock']);

        // Apply filters
        if ($request->filled('type_filter')) {
            $query->where('type', $request->type_filter);
        }

        // if ($request->filled('vendor_filter')) {
        //     $query->where('vendor_id', $request->vendor_filter);
        // }

        if ($request->filled('product_search')) {
            $search = $request->product_search;
            $query->whereHas('orderItem.product', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $records = $query->orderBy('created_at', 'desc')->paginate(15);

        // Summary data
        $totalDamaged = DamageReturnLost::where('status', 1)->sum('quantity');

        $totalLost = DamageReturnLost::where('status', 2)->sum('quantity');
        $totalValue = DamageReturnLost::sum('total_price');

        // Get vendors and products for filters
        $vendors = Vendor::orderBy('shop_name')->get();

        return view('product::damage-return-lost.index', compact(
            'records',
            'totalDamaged',
            'totalLost',
            'totalValue',
            'vendors'
        ));
    }

    /**
     * Show form for creating new damage/return/lost record
     */
    public function create(Request $request)
    {
        $orderId = $request->get('order_id');
        $order = null;
        $orderItems = collect();

        if ($orderId) {
            $order = Order::with(['vendor', 'orderItems.product', 'orderItems.orderItemStocks.stock'])
                ->find($orderId);
            if ($order) {
                $orderItems = $order->orderItems;
            }
        }

        $vendors = Vendor::orderBy('shop_name')->get();

        return view('product::damage-return-lost.create', compact('order', 'orderItems', 'vendors'));
    }

    /**
     * Store new damage/return/lost record
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:damage,return,lost',
            'order_id' => 'required|exists:orders,id',
            'order_item_id' => 'required|exists:order_items,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
            'unit_price' => 'required|numeric|min:0',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);


            DB::beginTransaction();

            $orderItem = OrderItem::with(['order.vendor', 'product', 'orderItemStocks.stock'])->find($request->order_item_id);

            // Validate quantity doesn't exceed available quantity
            $existingRecords = DamageReturnLost::where('order_item_id', $request->order_item_id)
                ->sum('quantity');
            $maxAllowed = $orderItem->quantity - $existingRecords;

            if ($request->quantity > $maxAllowed) {
                throw new \Exception("Cannot process {$request->quantity} items. Maximum allowed: {$maxAllowed}");
            }

            // Map type to status
            $statusMap = ['damage' => 1, 'return' => 2, 'lost' => 3];
            $status = $statusMap[$request->type];

            // Create damage/return/lost record
            $record = DamageReturnLost::create([
                'product_id' => $orderItem->product_id,
                'stock_id' => $orderItem->orderItemStocks->first()->stock_id,
                'order_id' => $request->order_id,
                'order_item_id' => $request->order_item_id,
                'quantity' => $request->quantity,
                'status' => $status,
                'purchase_price' => $orderItem->purchase_price,
                'total_price' => $request->quantity * $request->unit_price,
                'order_item_stock_id' => null, // Will be set after processing
                'reason' => $request->reason
            ]);

            // Process the quantity through order item stocks
            $this->processQuantityThroughStocks($orderItem, $request->quantity, $request->type, $record);

            // Update order item totals
            $this->updateOrderItemTotals($orderItem, $request->quantity, $request->type);

            // Update order totals
            $this->updateOrderTotals($orderItem->order, $request->quantity, $request->type,$record);

            // Update vendor account
            $this->updateVendorAccount($orderItem->order, $request->quantity * $request->unit_price, $request->type);

            // Handle image uploads using Spatie Media Library
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $record->addMedia($image)
                           ->toMediaCollection('evidence_pic');
                }
            }

            DB::commit();

            return redirect()->route('damage-return-lost.index')
                ->with('success', ucfirst($request->type) . ' record created successfully.');

    }

    /**
     * Display specific damage/return/lost record
     */
    public function show(DamageReturnLost $damageReturnLost)
    {
        $damageReturnLost->load(['order', 'orderItem.product', 'order.vendor', 'stock']);

        return view('product::damage-return-lost.show', compact('damageReturnLost'));
    }

    /**
     * Delete damage/return/lost record
     */
    public function destroy(DamageReturnLost $damageReturnLost)
    {
        try {
            DB::beginTransaction();

            // Get type from status
            $typeMap = [1 => 'damage', 2 => 'return', 3 => 'lost'];
            $type = $typeMap[$damageReturnLost->status];

            // Load relationships
            $damageReturnLost->load(['orderItem.orderItemStocks.stock', 'orderItem.order']);
            $orderItem = $damageReturnLost->orderItem;
            $order = $orderItem->order;

            // Reverse all the quantities and amounts
            $this->reverseQuantityThroughStocks($orderItem, $damageReturnLost->quantity, $type);

            // Update order item totals (reverse)
            $this->updateOrderItemTotals($orderItem, -$damageReturnLost->quantity, $type);

            // Update order totals (add back the amount)
            $order->total_amount += $damageReturnLost->total_price;

            // Update order totals (reverse the quantities)
            switch ($type) {
                case 'damage':
                    $order->total_damage_quantity = max(0, $order->total_damage_quantity - $damageReturnLost->quantity);
                    break;
                case 'return':
                    $order->total_return_quantity = max(0, $order->total_return_quantity - $damageReturnLost->quantity);
                    break;
                case 'lost':
                    $order->total_lost_quantity = max(0, $order->total_lost_quantity - $damageReturnLost->quantity);
                    break;
            }

            // Recalculate total_quantity
            $totalQuantity = $order->orderItems()->sum('quantity');
            $order->total_quantity = $totalQuantity;

            $order->save();

            // Update vendor account (add back the amount)
            $vendorAccount = \Modules\Product\Models\VendorAccount::where('order_id', $order->id)
                                                                  ->where('type', 1)
                                                                  ->first();

            if ($vendorAccount) {
                $vendorAccount->amount += $damageReturnLost->total_price;
                $vendorAccount->note = ($vendorAccount->note ?? '') .
                                     " | {$type} record deleted: +৳" . number_format($damageReturnLost->total_price, 2) .
                                     " on " . now()->format('Y-m-d H:i:s');
                $vendorAccount->save();
            }

            // Delete media files
            $damageReturnLost->clearMediaCollection('evidence_pic');

            // Delete the record
            $damageReturnLost->delete();

            DB::commit();

            return redirect()->route('damage-return-lost.index')
                ->with('success', 'Record deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to delete record: ' . $e->getMessage());
        }
    }

    /**
     * Search orders for damage/return/lost processing
     */
    public function searchOrders(Request $request)
    {
        $search = $request->get('search', '');

        $orders = Order::with(['vendor', 'orderItems.product'])
            ->where(function ($query) use ($search) {
                $query->where('invoice_id', 'LIKE', "%{$search}%")
                      ->orWhereHas('vendor', function ($q) use ($search) {
                          $q->where('shop_name', 'LIKE', "%{$search}%");
                      });
            })
            ->whereIn('order_status_id', [4, 5]) // Shipped or delivered
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'invoice_id' => $order->invoice_id,
                    'vendor_name' => $order->vendor->shop_name,
                    'total_amount' => $order->total_amount,
                    'items_count' => $order->orderItems->count(),
                    'created_at' => $order->created_at->format('M d, Y')
                ];
            });

        return response()->json($orders);
    }

    /**
     * Get order items for selected order
     */
    public function getOrderItems(Request $request)
    {
        $orderId = $request->get('order_id');

        $orderItems = OrderItem::with(['product', 'orderItemStocks.stock'])
            ->where('order_id', $orderId)
            ->get()
            ->map(function ($item) {
                $processedQty = DamageReturnLost::where('order_item_id', $item->id)->sum('quantity');
                $availableQty = $item->quantity - $processedQty;

                return [
                    'id' => $item->id,
                    'product_name' => $item->product->name ?? 'N/A',
                    'quantity' => $item->quantity,
                    'processed_quantity' => $processedQty,
                    'available_quantity' => $availableQty,
                    'unit_price' => $item->sell_price,
                    'total_amount' => $item->quantity * $item->sell_price
                ];
            });

        return response()->json($orderItems);
    }

    /**
     * Process quantity through order item stocks and update accordingly
     */
    private function processQuantityThroughStocks($orderItem, $quantity, $type, $damageRecord)
    {
        $remainingQuantity = $quantity;

        // Process through order item stocks (FIFO - First In, First Out)
        foreach ($orderItem->orderItemStocks as $orderItemStock) {
            if ($remainingQuantity <= 0) break;

            $availableInThisStock = $orderItemStock->quantity -
                                  $orderItemStock->return_quantity -
                                  $orderItemStock->damage_quantity -
                                  $orderItemStock->lost_quantity;

            if ($availableInThisStock <= 0) continue;

            $quantityToProcess = min($remainingQuantity, $availableInThisStock);

            // Update order item stock based on type
            switch ($type) {
                case 'damage':
                    $orderItemStock->damage_quantity += $quantityToProcess;
                    break;
                case 'return':
                    $orderItemStock->return_quantity += $quantityToProcess;
                    break;
                case 'lost':
                    $orderItemStock->lost_quantity += $quantityToProcess;
                    break;
            }

            $orderItemStock->save();

            // Update the main stock table
            $stock = $orderItemStock->stock;
            if ($stock) {
                switch ($type) {
                    case 'damage':
                        $stock->damage_quantity += $quantityToProcess;
                        break;
                    case 'return':
                        // For returns, decrease sold_quantity as requested
                        $stock->sold_quantity = max(0, $stock->sold_quantity - $quantityToProcess);
                        break;
                    case 'lost':
                        $stock->stolen_quantity += $quantityToProcess; // Using stolen_quantity for lost items
                        break;
                }
                $stock->save();
            }

            // Store which stock was affected (for the first one processed)
            if (!$damageRecord->stock_id && !$damageRecord->order_item_stock_id) {
                $damageRecord->update([
                    'stock_id' => $stock->id,
                    'order_item_stock_id' => $orderItemStock->id
                ]);
            }

            $remainingQuantity -= $quantityToProcess;
        }

        if ($remainingQuantity > 0) {
            throw new \Exception("Could not process all quantities. Remaining: {$remainingQuantity}");
        }
    }

    /**
     * Update order item totals
     */
    private function updateOrderItemTotals($orderItem, $quantity, $type)
    {
        switch ($type) {
            case 'damage':
                $orderItem->damage_quantity += $quantity;
                break;
            case 'return':
                $orderItem->return_quantity += $quantity;
                break;
            case 'lost':
                $orderItem->lost_quantity += $quantity;
                break;
        }

        $orderItem->save();
    }

    /**
     * Update order totals and amounts
     */
    private function updateOrderTotals($order, $quantity, $type, $record = null)
    {
        // Update the specific quantity totals based on type
        switch ($type) {
            case 'damage':
                $order->total_damage_quantity += $quantity;
                break;
            case 'return':
                $order->total_return_quantity += $quantity;
                break;
            case 'lost':
                $order->total_lost_quantity += $quantity;
                break;
        }

        // Recalculate total_quantity by summing up all order items
        $totalQuantity = $order->orderItems()->sum('quantity');
        $order->total_quantity = $totalQuantity;
        $order->total_quantity = $totalQuantity;
        // Decrease total_amount by the total price of the record
        if ($record) {
            $order->total_amount -= $record->total_price;
        }
        $order->save();
    }

    /**
     * Update vendor account to reflect the loss/return
     */
    private function updateVendorAccount($order, $amount, $type)
    {
        // Find existing vendor account record for this order
        $vendorAccount = \Modules\Product\Models\VendorAccount::where('order_id', $order->id)
                                                              ->where('type', 1) // Debit type
                                                              ->first();

        if ($vendorAccount) {
            // Reduce the vendor account amount as the order value has decreased
            $vendorAccount->amount -= $amount;

            // Ensure amount doesn't go negative
            $vendorAccount->amount = max(0, $vendorAccount->amount);

            $vendorAccount->note = ($vendorAccount->note ?? '') .
                                 " | {$type} adjustment: -৳" . number_format($amount, 2) .
                                 " on " . now()->format('Y-m-d H:i:s');

            $vendorAccount->save();
        }
    }

    /**
     * Reverse quantity through order item stocks (for quantity reductions)
     */
    private function reverseQuantityThroughStocks($orderItem, $quantity, $type)
    {
        $remainingQuantity = $quantity;

        // Reverse through order item stocks (LIFO - Last In, First Out for reversal)
        $orderItemStocks = $orderItem->orderItemStocks()->orderBy('id', 'desc')->get();

        foreach ($orderItemStocks as $orderItemStock) {
            if ($remainingQuantity <= 0) break;

            $currentAffectedQuantity = 0;

            // Get current affected quantity based on type
            switch ($type) {
                case 'damage':
                    $currentAffectedQuantity = $orderItemStock->damage_quantity;
                    break;
                case 'return':
                    $currentAffectedQuantity = $orderItemStock->return_quantity;
                    break;
                case 'lost':
                    $currentAffectedQuantity = $orderItemStock->lost_quantity;
                    break;
            }

            if ($currentAffectedQuantity <= 0) continue;

            $quantityToReverse = min($remainingQuantity, $currentAffectedQuantity);

            // Update order item stock by reducing the affected quantity
            switch ($type) {
                case 'damage':
                    $orderItemStock->damage_quantity -= $quantityToReverse;
                    break;
                case 'return':
                    $orderItemStock->return_quantity -= $quantityToReverse;
                    break;
                case 'lost':
                    $orderItemStock->lost_quantity -= $quantityToReverse;
                    break;
            }

            $orderItemStock->save();

            // Update the main stock table
            $stock = $orderItemStock->stock;
            if ($stock) {
                switch ($type) {
                    case 'damage':
                        $stock->damage_quantity = max(0, $stock->damage_quantity - $quantityToReverse);
                        break;
                    case 'return':
                        // For returns, increase sold_quantity back
                        $stock->sold_quantity += $quantityToReverse;
                        break;
                    case 'lost':
                        $stock->stolen_quantity = max(0, $stock->stolen_quantity - $quantityToReverse);
                        break;
                }

                $stock->save();
            }

            $remainingQuantity -= $quantityToReverse;
        }
    }

    /**
     * Test function to verify our logic (can be removed in production)
     */
    public function test()
    {
        try {
            // Get a sample order with items
            $order = Order::with(['orderItems.orderItemStocks.stock'])->first();

            if (!$order) {
                return response()->json(['error' => 'No orders found for testing']);
            }

            $orderItem = $order->orderItems->first();

            if (!$orderItem) {
                return response()->json(['error' => 'No order items found for testing']);
            }

            // Test data before
            $beforeData = [
                'order_item_stocks' => $orderItem->orderItemStocks->map(function($ois) {
                    return [
                        'id' => $ois->id,
                        'quantity' => $ois->quantity,
                        'damage_quantity' => $ois->damage_quantity,
                        'return_quantity' => $ois->return_quantity,
                        'lost_quantity' => $ois->lost_quantity,
                    ];
                }),
                'order_item' => [
                    'id' => $orderItem->id,
                    'quantity' => $orderItem->quantity,
                    'damage_quantity' => $orderItem->damage_quantity,
                    'return_quantity' => $orderItem->return_quantity,
                    'lost_quantity' => $orderItem->lost_quantity,
                ],
                'order' => [
                    'id' => $order->id,
                    'total_quantity' => $order->total_quantity,
                    'total_damage_quantity' => $order->total_damage_quantity,
                    'total_return_quantity' => $order->total_return_quantity,
                    'total_lost_quantity' => $order->total_lost_quantity,
                ],
                'stocks' => $orderItem->orderItemStocks->map(function($ois) {
                    return [
                        'id' => $ois->stock->id,
                        'quantity' => $ois->stock->quantity,
                        'sold_quantity' => $ois->stock->sold_quantity,
                        'damage_quantity' => $ois->stock->damage_quantity,
                        'stolen_quantity' => $ois->stock->stolen_quantity,
                    ];
                })
            ];

            return response()->json([
                'message' => 'Test data retrieved successfully',
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'before_data' => $beforeData,
                'instructions' => 'Use this data to test damage/return/lost creation'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Test failed: ' . $e->getMessage()]);
        }
    }
}
