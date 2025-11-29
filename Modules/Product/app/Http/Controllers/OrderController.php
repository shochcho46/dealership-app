<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Order;
use Modules\Product\Models\OrderItem;
use Modules\Product\Models\Product;
use Modules\Product\Models\Stock;
use Modules\Product\Models\OrderStatus;
use Modules\Product\Models\Vendor;
use App\Models\Admin;
use Modules\Product\Models\OrderItemStock;
use Modules\Product\Models\VendorAccount;
use Modules\Product\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['vendor', 'orderStatus', 'orderItems.product']);

        // Apply filters
        if ($request->filled('invoice_search')) {
            $query->where('invoice_id', 'like', '%' . $request->invoice_search . '%');
        }

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
        if ($request->filled('place_by_filter')) {
            $query->where('place_by', $request->place_by_filter);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        // Summary data
        $totalOrders = Order::count();
        $totalAmount = Order::sum('total_amount');
        $pendingOrders = Order::where('order_status_id', 1)->count(); // Assuming 1 is pending
        $completedOrders = Order::whereIn('order_status_id', [4, 5])->count(); // Shipped/Delivered

        // Get all order statuses and vendors for filters
        $limit = request()->get('limit', 30);
        $orderStatuses = OrderStatus::orderBy('id')->paginate($limit);
        $vendors = Vendor::orderBy('shop_name')->get();

        $placeBys = Admin::role(['admin', 'subadmin', 'dsr', 'sr'])->orderBy('name')->get();

        return view('product::order.index', compact(
            'orders',
            'totalOrders',
            'totalAmount',
            'pendingOrders',
            'completedOrders',
            'orderStatuses',
            'vendors',
            'placeBys'
        ));
    }

    /**
     * Show the form for creating a new order
     */
    public function create()
    {
        $products = Product::active()->with(['stocks' => function ($query) {
            $query->whereRaw('quantity > (sold_quantity + damage_quantity + stolen_quantity + transfer_quantity + froze_quantity)');
        }])->get();

        $vendors = Vendor::active()->get();
        $orderStatuses = OrderStatus::active()->get();

        // Get admins with roles: admin, subadmin, dsr, sr
        $admins = Admin::role(['admin', 'subadmin', 'dsr', 'sr'])->orderBy('name')->get();

        return view('product::order.create', compact('products', 'vendors', 'orderStatuses', 'admins'));
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'place_by' => 'required|exists:admins,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.sell_price' => 'required|numeric|min:0',
            'items.*.discount_price' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Get confirmed status (assuming it exists)
            $confirmedStatus = OrderStatus::where('name', 'Confirmed')->first();
            if (!$confirmedStatus) {
                $confirmedStatus = OrderStatus::first(); // Fallback to first status
            }

            // Create the order
            $order = Order::create([
                'admin_id' => Auth::guard('admin')->id(),
                'vendor_id' => $request->vendor_id,
                'place_by' => $request->place_by,
                'order_status_id' => $confirmedStatus->id,
                'payment_status' => 0, // Unpaid
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
                    'purchase_price' => 0, // Will be calculated from stocks
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

            return redirect()->route('orders.index')
                           ->with('success', 'Order created successfully with Invoice ID: ' . $order->invoice_id);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error creating order: ' . $e->getMessage());
        }
    }

    /**
     * Allocate stock for an order item using smart allocation
     */
    private function allocateStockForOrderItem($orderItem, $requestedQuantity)
    {
        $product = Product::find($orderItem->product_id);
        $availableStocks = Stock::where('product_id', $product->id)
            ->whereRaw('quantity > (sold_quantity + damage_quantity + stolen_quantity + froze_quantity)')
            ->orderBy('sell_price', 'desc') // Highest price first for profit
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

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $order->load(['admin', 'orderStatus', 'vendor', 'orderItems.product', 'orderItems.orderItemStocks.stock']);

        return view('product::order.show', compact('order'));
    }

    /**
     * Show the form for editing the order
     */
    public function edit(Order $order)
    {
        if (!$order->canBeCancelled()) {
            return redirect()->route('orders.index')
                           ->with('error', 'This order cannot be edited.');
        }

        $order->load(['orderItems.product', 'orderItems.stock']);
        $products = Product::active()->with(['stocks' => function ($query) {
            $query->whereRaw('quantity > (sold_quantity + damage_quantity + stolen_quantity + transfer_quantity + froze_quantity)');
        }])->get();

        $vendors = Vendor::active()->get();
        $orderStatuses = OrderStatus::active()->get();

        // Get admins with roles: admin, subadmin, dsr, sr
        $admins = Admin::role(['admin', 'subadmin', 'dsr', 'sr'])->orderBy('name')->get();

        return view('product::order.edit', compact('order', 'products', 'vendors', 'orderStatuses', 'admins'));
    }

    /**
     * Update the specified order
     */
    public function update(Request $request, Order $order)
    {
        if (!$order->canBeCancelled()) {
            return redirect()->route('orders.index')
                           ->with('error', 'This order cannot be updated.');
        }

        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'place_by' => 'required|exists:admins,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.sell_price' => 'required|numeric|min:0',
            'items.*.discount_price' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // First, restore stock quantities from existing order items
            foreach ($order->orderItems as $existingItem) {
                // Restore stock from order item stocks
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
                    'purchase_price' => 0, // Will be calculated from stocks
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

            return redirect()->route('orders.index')
                           ->with('success', 'Order updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error updating order: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the specified order
     */
    public function cancel(Order $order)
    {
        Log::info('Attempting to cancel order ID: ' . $order->id);
        if ($order->cancelOrder()) {
            return redirect()->route('orders.index')
                           ->with('success', 'Order cancelled successfully and stock restored.');
        }

        return redirect()->route('orders.index')
                       ->with('error', 'Unable to cancel this order.');
    }

    /**
     * Show cancelled orders
     */
    public function cancelled(Request $request)
    {
        $query = Order::cancelled()->with(['admin', 'orderStatus', 'vendor', 'orderItems.product']);

        // Search filters
        if ($request->filled('invoice_search')) {
            $query->where('invoice_id', 'like', '%' . $request->invoice_search . '%');
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

        $orders = $query->latest()->paginate(15)->withQueryString();
        $vendors = Vendor::active()->get();

        return view('product::order.cancelled', compact('orders', 'vendors'));
    }

    /**
     * Get product details via AJAX
     */
    public function getProductDetails(Request $request)
    {
        try {
            if ($request->product_id === 'all') {
                $products = Product::active()
                    ->whereHas('stocks', function ($query) {
                        $query->whereRaw('quantity > (sold_quantity + damage_quantity + stolen_quantity + COALESCE(froze_quantity, 0))');
                    })
                    ->with('stocks')
                    ->get(['id', 'name'])
                    ->map(function ($product) {
                        // Calculate available quantity correctly
                        $availableQuantity = 0;
                        if ($product->stocks) {
                            $availableQuantity = $product->stocks->sum(function ($stock) {
                                return max(0, $stock->quantity - $stock->sold_quantity - $stock->damage_quantity - $stock->stolen_quantity - ($stock->froze_quantity ?? 0));
                            });
                        }

                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'image_url' => $product->product_image_thumb_url,
                            'available_quantity' => $availableQuantity
                        ];
                    });

                return response()->json(['products' => $products]);
            }

            $product = Product::with(['stocks' => function ($query) {
                $query->whereRaw('quantity > (sold_quantity + damage_quantity + stolen_quantity + COALESCE(froze_quantity, 0))')
                      ->orderBy('sell_price', 'desc');
            }])->find($request->product_id);

            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            $totalAvailableQuantity = $product->stocks->sum(function ($stock) {
                return max(0, $stock->quantity - $stock->sold_quantity - $stock->damage_quantity - $stock->stolen_quantity - ($stock->froze_quantity ?? 0));
            });

            $highestSellPrice = $product->stocks->max('sell_price') ?? 0;

            return response()->json([
                'product' => $product,
                'available_quantity' => $totalAvailableQuantity,
                'highest_sell_price' => $highestSellPrice,
                'image_url' => $product->product_image_thumb_url,
                'stocks' => $product->stocks->map(function ($stock) {
                    return [
                        'id' => $stock->id,
                        'batch_id' => $stock->batch_id,
                        'sell_price' => $stock->sell_price,
                        'available_quantity' => max(0, $stock->quantity - $stock->sold_quantity - $stock->damage_quantity - $stock->stolen_quantity - ($stock->froze_quantity ?? 0)),
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load product details: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get stock details via AJAX
     */
    public function getStockDetails(Request $request)
    {
        $stock = Stock::with('product')->find($request->stock_id);

        if (!$stock) {
            return response()->json(['error' => 'Stock not found'], 404);
        }

        $availableQuantity = $stock->quantity - $stock->sold_quantity -
                           $stock->damage_quantity - $stock->stolen_quantity -
                           $stock->froze_quantity;

        return response()->json([
            'stock' => $stock,
            'available_quantity' => $availableQuantity,
            'sell_price' => $stock->sell_price,
        ]);
    }

    /**
     * Search vendors via AJAX
     */
    public function searchVendors(Request $request)
    {
        $search = $request->get('query', $request->get('search', ''));

        $vendors = Vendor::where('shop_name', 'LIKE', "%{$search}%")
            ->orWhere('mobile', 'LIKE', "%{$search}%")
            ->orWhere('contact_person', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get(['id', 'shop_name', 'mobile', 'contact_person', 'full_address']);

        return response()->json($vendors);
    }

    /**
     * Update multiple order statuses
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'status_id' => 'required|exists:order_statuses,id'
        ]);

         $orders = Order::whereIn('id', $request->order_ids)->get();
         foreach ($orders as $order) {
             if ($order->order_status_id == 4 || $order->order_status_id == 5 || $order->order_status_id == 6) {
                return response()->json(['message' => 'Orders status cannot be changed as they are already shipped, delivered, or cancelled.'], 400);
             }
            }

        DB::beginTransaction();
        try {
            $orders = Order::whereIn('id', $request->order_ids)->get();
            $orderStatus = OrderStatus::find($request->status_id);

            foreach ($orders as $order) {
                $order->update(['order_status_id' => $request->status_id]);

                // If status is shipped (4) or delivered (5), convert froze to sold and create vendor account record
                if (in_array($request->status_id, [4, 5])) {
                    foreach ($order->orderItems as $orderItem) {
                        foreach ($orderItem->orderItemStocks as $orderItemStock) {
                            $stock = $orderItemStock->stock;

                            // Convert froze quantity to sold quantity
                            $stock->froze_quantity = max(0, $stock->froze_quantity - $orderItemStock->quantity);
                            $stock->sold_quantity += $orderItemStock->quantity;
                            $stock->save();
                        }
                    }

                    // Create vendor account record
                    VendorAccount::create([
                        'vendor_id' => $order->vendor_id,
                        'order_id' => $order->id,
                        'amount' => $order->total_amount,
                        'type' => 1, // Debit
                        'deposite_by' => $order->admin_id,
                        'note' => 'Product order - ' . $order->invoice_id
                    ]);
                }
            }

            DB::commit();

             return response()->json([
                            'status' => true,
                            'message' => 'Orders updated successfully',
                            'alert_type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
