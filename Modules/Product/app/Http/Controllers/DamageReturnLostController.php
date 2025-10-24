<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Product\Models\DamageReturnLost;
use Modules\Product\Models\Order;
use Modules\Product\Models\OrderItem;
use Modules\Product\Models\Product;
use Modules\Product\Models\Stock;
use Modules\Product\Models\Vendor;

class DamageReturnLostController extends Controller
{
    /**
     * Display listing of damage/return/lost items
     */
    public function index(Request $request)
    {
        $query = DamageReturnLost::with(['order', 'orderItem.product', 'vendor', 'stock']);
        
        // Apply filters
        if ($request->filled('type_filter')) {
            $query->where('type', $request->type_filter);
        }
        
        if ($request->filled('vendor_filter')) {
            $query->where('vendor_id', $request->vendor_filter);
        }
        
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
        
        try {
            DB::beginTransaction();
            
            $orderItem = OrderItem::with(['order', 'product'])->find($request->order_item_id);
            
            // Validate quantity doesn't exceed available quantity
            $existingRecords = DamageReturnLost::where('order_item_id', $request->order_item_id)
                ->sum('quantity');
            $maxAllowed = $orderItem->quantity - $existingRecords;
            
            if ($request->quantity > $maxAllowed) {
                throw new \Exception("Cannot process {$request->quantity} items. Maximum allowed: {$maxAllowed}");
            }
            
            // Create damage/return/lost record
            $record = DamageReturnLost::create([
                'type' => $request->type,
                'order_id' => $request->order_id,
                'order_item_id' => $request->order_item_id,
                'vendor_id' => $orderItem->order->vendor_id,
                'stock_id' => $request->stock_id ?? null,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'total_amount' => $request->quantity * $request->unit_price,
                'reason' => $request->reason,
                'reported_by' => auth('admin')->user()->name ?? 'System',
                'reported_at' => now()
            ]);
            
            // Handle image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $record->addMedia($image)
                        ->usingName($request->type . ' evidence')
                        ->toMediaCollection('evidence');
                }
            }
            
            // Update order totals based on type
            $order = $orderItem->order;
            
            switch ($request->type) {
                case 1:
                    $order->total_damage_quantity += $request->quantity;
                    break;
                case 2:
                case 'lost':
                    $order->total_lost_quantity += $request->quantity;
                    break;
            }
            
            $order->save();
            
            
            
            DB::commit();
            
            return redirect()->route('damage-return-lost.index')
                ->with('success', ucfirst($request->type) . ' record created successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Failed to create record: ' . $e->getMessage());
        }
    }
    
    /**
     * Display specific damage/return/lost record
     */
    public function show(DamageReturnLost $damageReturnLost)
    {
        $damageReturnLost->load(['order', 'orderItem.product', 'vendor', 'stock']);
        
        return view('product::damage-return-lost.show', compact('damageReturnLost'));
    }
    
    /**
     * Show form for editing record
     */
    public function edit(DamageReturnLost $damageReturnLost)
    {
        $damageReturnLost->load(['order', 'orderItem.product', 'vendor']);
        
        return view('product::damage-return-lost.edit', compact('damageReturnLost'));
    }
    
    /**
     * Update damage/return/lost record
     */
    public function update(Request $request, DamageReturnLost $damageReturnLost)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);
        
        try {
            DB::beginTransaction();
            
            $oldQuantity = $damageReturnLost->quantity;
            $quantityDiff = $request->quantity - $oldQuantity;
            
            // Update record
            $damageReturnLost->update([
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'total_amount' => $request->quantity * $request->unit_price,
                'reason' => $request->reason
            ]);
            
            // Handle new image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $damageReturnLost->addMedia($image)
                        ->usingName($damageReturnLost->type . ' evidence')
                        ->toMediaCollection('evidence');
                }
            }
            
            // Update order totals
            $order = $damageReturnLost->order;
            
            switch ($damageReturnLost->type) {
                case 1:
                    $order->total_damage_quantity += $quantityDiff;
                    break;
                case 2:
                    $order->total_lost_quantity += $quantityDiff;
                    break;
            }
            
            $order->save();
            
            DB::commit();
            
            return redirect()->route('damage-return-lost.show', $damageReturnLost)
                ->with('success', 'Record updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Failed to update record: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete damage/return/lost record
     */
    public function destroy(DamageReturnLost $damageReturnLost)
    {
        try {
            DB::beginTransaction();
            
            // Update order totals
            $order = $damageReturnLost->order;
            
            switch ($damageReturnLost->type) {
                case 1:
                    $order->total_damage_quantity -= $damageReturnLost->quantity;
                    break;
                
                case 2:
                    $order->total_lost_quantity -= $damageReturnLost->quantity;
                    break;
            }
            
            $order->save();
            
            // Delete record and its media
            $damageReturnLost->clearMediaCollection('evidence');
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
     * Restock returned items
     */
    private function restockItems(OrderItem $orderItem, int $quantity)
    {
        // Find the best stock to restock (highest price first)
        $stocks = Stock::where('product_id', $orderItem->product_id)
            ->orderBy('purchase_price', 'desc')
            ->get();
            
        $remainingToRestock = $quantity;
        
        foreach ($stocks as $stock) {
            if ($remainingToRestock <= 0) break;
            
            $stock->quantity += $remainingToRestock;
            $stock->save();
            break; // For simplicity, add all to the first (highest price) stock
        }
    }
}