<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Stock;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Modules\Product\Models\Product;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 50); // Default to 15 if not provided
        $query = Stock::with(['product', 'product.color', 'product.unit']);

        // Search by product name
        if ($request->filled('product_search')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->product_search . '%');
            });
        }

        // Search by batch ID
        if ($request->filled('batch_search')) {
            $query->where('batch_id', 'like', '%' . $request->batch_search . '%');
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $stocks = $query->latest()->paginate( $limit);

        return view('product::stock.index', compact('stocks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::active()->with(['color', 'unit'])->get();
        return view('product::stock.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $stocks = $request->stocks;

        // Validate dynamically
        foreach ($stocks as $index => $stockData) {
            $validator = Validator::make($stockData, [
                'product_id' => 'required|exists:products,id',
                'purchase_price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:1',
                'sell_price' => 'required|numeric|min:0',
                'manufacture_date' => 'nullable|date',
                'expire_date' => 'nullable|date|after_or_equal:manufacture_date',
            ], [], [
                'product_id' => "Product at row " . ($index + 1),
                'purchase_price' => "Purchase Price at row " . ($index + 1),
                'quantity' => "Quantity at row " . ($index + 1),
                'sell_price' => "Sell Price at row " . ($index + 1),
                'manufacture_date' => "Manufacture Date at row " . ($index + 1),
                'expire_date' => "Expire Date at row " . ($index + 1),
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
        }

        // DB::transaction(function () use ($stocks) {
            foreach ($stocks as $stockData) {
            $batchId = Stock::generateBatchId();
            $totalPrice = $stockData['purchase_price'] * $stockData['quantity'];

            Stock::create([
                'product_id' => $stockData['product_id'],
                'batch_id' => $batchId,
                'purchase_price' => $stockData['purchase_price'],
                'quantity' => $stockData['quantity'],
                'total_price' => $totalPrice,
                'sell_price' => $stockData['sell_price'],
                'damage_quantity' => $stockData['damage_quantity'] ?? 0,
                'sold_quantity' =>  0,
                'stolen_quantity' => $stockData['stolen_quantity'] ?? 0,
                'transfer_quantity' =>  0,
                'status' => 1,
                'warehouse_id' => 1,
                'manufacture_date' => $stockData['manufacture_date'] ?? null,
                'expire_date' => $stockData['expire_date'] ?? null,
            ]);
            }
        // });

         return redirect()->route('admin.stockIndex')->with('success', 'Stock entries created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Stock $stock)
    {
        $stock->load(['product', 'product.color', 'product.unit']);
        return view('product::stock.show', compact('stock'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Stock $stock)
    {
        // Prevent editing if stock has sold or frozen quantity
        if ($stock->sold_quantity > 0 || $stock->froze_quantity > 0) {
            return redirect()->route('admin.stockIndex')
                ->with('error', 'Cannot edit this stock. It has sold quantity (' . $stock->sold_quantity . ') or frozen quantity (' . $stock->froze_quantity . ').');
        }

        $products = Product::active()->with(['color', 'unit'])->get();
        return view('product::stock.edit', compact('stock', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Stock $stock)
    {
        // Prevent updating if stock has sold or frozen quantity
        if ($stock->sold_quantity > 0 || $stock->froze_quantity > 0) {
            return redirect()->route('admin.stockIndex')
                ->with('error', 'Cannot update this stock. It has sold quantity (' . $stock->sold_quantity . ') or frozen quantity (' . $stock->froze_quantity . ').');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'purchase_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'sell_price' => 'required|numeric|min:0',
            'damage_quantity' => 'nullable|integer|min:0',
            'sold_quantity' => 'nullable|integer|min:0',
            'stolen_quantity' => 'nullable|integer|min:0',
            'transfer_quantity' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
            'manufacture_date' => 'nullable|date',
            'expire_date' => 'nullable|date|after_or_equal:manufacture_date',
        ]);

        try {
            // Calculate total price
            $totalPrice = $request->purchase_price * $request->quantity;

            $stock->update([
                'product_id' => $request->product_id,
                'purchase_price' => $request->purchase_price,
                'quantity' => $request->quantity,
                'total_price' => $totalPrice,
                'sell_price' => $request->sell_price,
                'damage_quantity' => $request->damage_quantity ?? 0,
                'sold_quantity' => $request->sold_quantity ?? 0,
                'stolen_quantity' => $request->stolen_quantity ?? 0,
                'transfer_quantity' => $request->transfer_quantity ?? 0,
                'status' => $request->status ?? 1,
                'manufacture_date' => $request->manufacture_date,
                'expire_date' => $request->expire_date,
            ]);

            return redirect()->route('admin.stockIndex')->with('success', 'Stock updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update stock: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Stock $stock)
    {
        if ($stock->sold_quantity > 0) {
            return redirect()->back()->with('error', 'This stock cannot be deleted because it has sold quantity.');
        }
        try {
            $stock->delete();
            return redirect()->route('admin.stockIndex')->with('success', 'Stock deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete stock: ' . $e->getMessage());
        }
    }

    /**
     * Get product details for AJAX
     */
    public function getProductDetails(Request $request)
    {
        $product = Product::with(['color', 'unit'])->find($request->product_id);

        if ($product) {
            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'color' => $product->color->name ?? 'N/A',
                    'unit' => $product->unit->name ?? 'N/A',
                ]
            ]);
        }

        return response()->json(['success' => false]);
    }

    /**
     * Search products for stock creation
     */
    public function searchProducts(Request $request)
    {
        $search = $request->get('q', '');

        $products = Product::active()
            ->with(['color', 'unit', 'stocks'])
            ->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");

            })
            ->limit(100)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'color' => $product->color->name ?? null,
                    'unit' => $product->unit->name ?? null,
                    'image' => $product->product_image_thumb_url,
                    'total_stock' => $product->stocks->sum('remaining_quantity')
                ];
            });

        return response()->json($products);
    }
}
