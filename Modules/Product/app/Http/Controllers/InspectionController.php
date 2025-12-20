<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Inspection;
use Modules\Product\Models\InspectionItem;
use Modules\Product\Models\Product;
use Modules\Product\Models\Stock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InspectionController extends Controller
{
    /**
     * Display a listing of inspections
     */
    public function index()
    {
        $inspections = Inspection::with('inspectedBy')
            ->latest()
            ->paginate(30);

        return view('product::inspection.index', compact('inspections'));
    }

    /**
     * Show the form for creating a new inspection
     */
    public function create()
    {
        // Get all stocks with their product data (stock-by-stock basis)
        $stocks = Stock::with(['product'])
            ->whereHas('product')
            ->get()
            ->map(function ($stock) {
                // Calculate available quantity for this specific stock
                $availableQty = $stock->quantity
                    - $stock->sold_quantity
                    - $stock->damage_quantity
                    - $stock->stolen_quantity
                    - $stock->froze_quantity;

                return [
                    'stock_id' => $stock->id,
                    'product_id' => $stock->product_id,
                    'product_name' => $stock->product->name,
                    'product_image' => $stock->product->product_image_thumb_url,
                    'purchase_price' => $stock->purchase_price,
                    'sell_price' => $stock->sell_price,
                    'total_qty' => $stock->quantity,
                    'sold_qty' => $stock->sold_quantity,
                    'existing_damage_qty' => $stock->damage_quantity,
                    'existing_stolen_qty' => $stock->stolen_quantity,
                    'froze_qty' => $stock->froze_quantity,
                    'available_qty' => $availableQty,
                    'system_qty' => $availableQty,
                    'manufacture_date' => $stock->manufacture_date,
                    'expire_date' => $stock->expire_date,
                ];
            })
            ->filter(function ($stock) {
                // Only show stocks with available quantity
                return $stock['available_qty'] > 0;
            })
            ->sortBy('product_name')
            ->values();

        return view('product::inspection.create', compact('stocks'));
    }

    /**
     * Store a newly created inspection
     */
    public function store(Request $request)
    {
        $request->validate([
            'inspection_date' => 'required|date',
            'notes' => 'nullable|string',
            'stocks' => 'required|array',
            'stocks.*.stock_id' => 'required|exists:stocks,id',
            'stocks.*.product_id' => 'required|exists:products,id',
            'stocks.*.system_qty' => 'required|integer|min:0',
            'stocks.*.physical_qty' => 'required|integer|min:0',
            'stocks.*.damage_qty' => 'nullable|integer|min:0',
            'stocks.*.lost_qty' => 'nullable|integer|min:0',
            'stocks.*.purchase_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Calculate totals
            $totalDamageQty = 0;
            $totalLostQty = 0;
            $totalDamageAmount = 0;
            $totalLostAmount = 0;

            foreach ($request->stocks as $stockData) {
                $damageQty = $stockData['damage_qty'] ?? 0;
                $lostQty = $stockData['lost_qty'] ?? 0;
                $purchasePrice = $stockData['purchase_price'];

                $totalDamageQty += $damageQty;
                $totalLostQty += $lostQty;
                $totalDamageAmount += ($damageQty * $purchasePrice);
                $totalLostAmount += ($lostQty * $purchasePrice);
            }

            // Create inspection
            $inspection = Inspection::create([
                'inspection_date' => $request->inspection_date,
                'notes' => $request->notes,
                'total_damage_amount' => $totalDamageAmount,
                'total_lost_amount' => $totalLostAmount,
                'total_damage_qty' => $totalDamageQty,
                'total_lost_qty' => $totalLostQty,
                'inspected_by' => Auth::guard('admin')->id(),
            ]);

            // Create inspection items
            foreach ($request->stocks as $stockData) {
                // Only save items that have damage or lost quantities
                if (($stockData['damage_qty'] ?? 0) > 0 || ($stockData['lost_qty'] ?? 0) > 0) {
                    InspectionItem::create([
                        'inspection_id' => $inspection->id,
                        'stock_id' => $stockData['stock_id'],
                        'product_id' => $stockData['product_id'],
                        'system_qty' => $stockData['system_qty'],
                        'physical_qty' => $stockData['physical_qty'],
                        'damage_qty' => $stockData['damage_qty'] ?? 0,
                        'lost_qty' => $stockData['lost_qty'] ?? 0,
                        'damage_amount' => ($stockData['damage_qty'] ?? 0) * $stockData['purchase_price'],
                        'lost_amount' => ($stockData['lost_qty'] ?? 0) * $stockData['purchase_price'],
                        'avg_purchase_price' => $stockData['purchase_price'],
                        'remarks' => $stockData['remarks'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.inspectionIndex')
                ->with('success', 'Inspection created successfully! Inspection Number: ' . $inspection->inspection_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create inspection: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified inspection
     */
    public function show(Inspection $inspection)
    {
        $inspection->load(['items.product', 'items.stock', 'inspectedBy']);

        return view('product::inspection.show', compact('inspection'));
    }

    /**
     * Remove the specified inspection
     */
    public function destroy(Inspection $inspection)
    {
        try {
            $inspection->delete();
            return redirect()->route('admin.inspectionIndex')
                ->with('success', 'Inspection deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete inspection: ' . $e->getMessage());
        }
    }
}
