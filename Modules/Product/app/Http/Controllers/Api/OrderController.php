<?php

namespace Modules\Product\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Product\Models\Order;
use Modules\Product\Models\OrderItem;
use Modules\Product\Models\OrderItemStock;
use Modules\Product\Models\OrderStatus;
use Modules\Product\Models\Product;
use Modules\Product\Models\Stock;
use Modules\Product\Http\Resources\OrderResource;

class OrderController extends Controller
{
    /**
     * Create a new order (API version)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'vendor_id' => 'required|exists:vendors,id',
                'place_by' => 'required|exists:admins,id',
                'latitude' => 'nullable|string',
                'longitude' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.sell_price' => 'required|numeric|min:0',
                'items.*.discount_price' => 'nullable|numeric|min:0',
            ]);

            DB::beginTransaction();

            // Get confirmed status
            $confirmedStatus = OrderStatus::where('name', 'Confirmed')->first();
            if (!$confirmedStatus) {
                $confirmedStatus = OrderStatus::first();
            }

            // Create the order
            $order = Order::create([
                'admin_id' => $request->user()->id,
                'vendor_id' => $request->vendor_id,
                'place_by' => $request->place_by,
                'order_status_id' => $confirmedStatus->id,
                'payment_status' => 0,
                'total_amount' => 0,
                'total_quantity' => 0,
                'total_discount_amount' => 0,
            ]);

            $totalAmount = 0;
            $totalQuantity = 0;
            $totalDiscount = 0;

            // Create order items with smart stock allocation
            foreach ($request->items as $item) {
                $discountPrice = $item['discount_price'] ?? 0;
                $totalPrice = ($item['sell_price'] * $item['quantity']) - $discountPrice;

                // Create order item
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'purchase_price' => 0,
                    'sell_price' => $item['sell_price'],
                    'total_price' => $totalPrice,
                    'discount_price' => $discountPrice,
                ]);

                // Smart stock allocation
                $this->allocateStockForOrderItem($orderItem, $item['quantity']);

                $totalAmount += $totalPrice;
                $totalQuantity += $item['quantity'];
                $totalDiscount += $discountPrice;
            }

            // Update order totals
            $order->update([
                'total_amount' => $totalAmount,
                'total_quantity' => $totalQuantity,
                'total_discount_amount' => $totalDiscount,
            ]);

            DB::commit();

            // Load relationships for response
            $order->load(['admin', 'vendor', 'orderStatus', 'placeBy', 'orderItems.product.media', 'orderItems.orderItemStocks.stock']);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => new OrderResource($order)
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating order via API: ' . $e->getMessage());
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing order (API version)
     * Checks if order is confirmed before allowing update
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);

            // Check if order can be updated (not confirmed/delivered/cancelled)
            if (!$order->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This order cannot be updated. It has been confirmed or is in a final state.'
                ], 403);
            }

            $validated = $request->validate([
                'vendor_id' => 'required|exists:vendors,id',
                'place_by' => 'required|exists:admins,id',
                'latitude' => 'nullable|string',
                'longitude' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.sell_price' => 'required|numeric|min:0',
                'items.*.discount_price' => 'nullable|numeric|min:0',
            ]);

            DB::beginTransaction();

            // Restore stock quantities from existing order items
            foreach ($order->orderItems as $existingItem) {
                foreach ($existingItem->orderItemStocks as $orderItemStock) {
                    $stock = $orderItemStock->stock;
                    if ($stock) {
                        $stock->froze_quantity = max(0, $stock->froze_quantity - $orderItemStock->quantity);
                        $stock->save();
                    }
                }
            }

            // Delete existing order items and their stocks
            foreach ($order->orderItems as $existingItem) {
                $existingItem->orderItemStocks()->delete();
            }
            $order->orderItems()->delete();

            $totalAmount = 0;
            $totalQuantity = 0;
            $totalDiscount = 0;

            // Create new order items with smart stock allocation
            foreach ($request->items as $item) {
                $discountPrice = $item['discount_price'] ?? 0;
                $totalPrice = ($item['sell_price'] * $item['quantity']) - $discountPrice;

                // Create order item
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'purchase_price' => 0,
                    'sell_price' => $item['sell_price'],
                    'total_price' => $totalPrice,
                    'discount_price' => $discountPrice,
                ]);

                // Smart stock allocation for the new order item
                $this->allocateStockForOrderItem($orderItem, $item['quantity']);

                $totalAmount += $totalPrice;
                $totalQuantity += $item['quantity'];
                $totalDiscount += $discountPrice;
            }

            // Update order totals
            $order->update([
                'vendor_id' => $request->vendor_id,
                'place_by' => $request->place_by,
                'total_amount' => $totalAmount,
                'total_quantity' => $totalQuantity,
                'total_discount_amount' => $totalDiscount,
            ]);

            DB::commit();

            // Load relationships for response
            $order->load(['admin', 'vendor', 'orderStatus', 'placeBy', 'orderItems.product.media', 'orderItems.orderItemStocks.stock']);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => new OrderResource($order)
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error updating order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel an order
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($id)
    {
        try {
            $order = Order::findOrFail($id);

            if (!$order->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This order cannot be cancelled.'
                ], 403);
            }

            if ($order->cancelOrder()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order cancelled successfully and stock restored.'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unable to cancel this order.'
            ], 400);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get orders placed by a specific user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByPlacedBy(Request $request)
    {   
        try {
            $request->validate([
                'place_by' => 'required|exists:admins,id',
                'status_filter' => 'nullable|exists:order_statuses,id',
                'vendor_filter' => 'nullable|exists:vendors,id',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $query = Order::with(['admin', 'vendor', 'orderStatus', 'placeBy', 'orderItems.product.media'])
                ->where('place_by', $request->place_by);
                
            // Apply filters
            if ($request->filled('status_filter')) {
                $query->where('order_status_id', $request->status_filter);
            }

            if ($request->filled('vendor_filter')) {
                $query->where('vendor_id', $request->vendor_filter);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $perPage = $request->input('per_page', 15);
            $orders = $query->orderBy('id', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => OrderResource::collection($orders),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
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
                'message' => 'Error fetching orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order details by ID
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $order = Order::with(['admin', 'vendor', 'orderStatus', 'placeBy', 'orderItems.product.media', 'orderItems.orderItemStocks.stock'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new OrderResource($order)
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Allocate stock for an order item using smart allocation
     * 
     * @param OrderItem $orderItem
     * @param int $requestedQuantity
     * @throws \Exception
     */
    private function allocateStockForOrderItem($orderItem, $requestedQuantity)
    {
        $product = Product::find($orderItem->product_id);
        $availableStocks = Stock::where('product_id', $product->id)
            ->whereRaw('quantity > (sold_quantity + damage_quantity + stolen_quantity + froze_quantity)')
            ->orderBy('sell_price', 'desc')
            ->get();

        $remainingQuantity = $requestedQuantity;
        $totalPurchasePrice = 0;

        foreach ($availableStocks as $stock) {
            if ($remainingQuantity <= 0) break;

            $availableQuantity = $stock->quantity - $stock->sold_quantity -
                               $stock->damage_quantity - $stock->stolen_quantity - $stock->froze_quantity;

            if ($availableQuantity <= 0) continue;

            $allocateQuantity = min($remainingQuantity, $availableQuantity);

            // Create order item stock record
            OrderItemStock::create([
                'orderitem_id' => $orderItem->id,
                'stock_id' => $stock->id,
                'quantity' => $allocateQuantity,
                'purchase_price' => $stock->purchase_price,
                'sell_price' => $orderItem->sell_price,
                'total_price' => $orderItem->sell_price * $allocateQuantity,
                'discount_amount' => ($orderItem->discount_price / $orderItem->quantity) * $allocateQuantity,
                'actual_profit' => ($orderItem->sell_price - $stock->purchase_price) * $allocateQuantity,
            ]);

            // Update stock froze quantity
            $stock->froze_quantity += $allocateQuantity;
            $stock->save();

            $totalPurchasePrice += $stock->purchase_price * $allocateQuantity;
            $remainingQuantity -= $allocateQuantity;
        }

        if ($remainingQuantity > 0) {
            throw new \Exception("Insufficient stock for {$product->name}. Still need: {$remainingQuantity}");
        }

        // Update order item with average purchase price
        $orderItem->update([
            'purchase_price' => $totalPurchasePrice / $requestedQuantity
        ]);
    }
}
