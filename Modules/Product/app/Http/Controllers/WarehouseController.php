<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Warehouse;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = Warehouse::latest()->paginate(10);
        return view('product::warehouse.index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('product::warehouse.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'mobile' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        try {
            Warehouse::create($request->all());
            return redirect()->route('admin.warehouseIndex')
                           ->with('success', 'Warehouse created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Failed to create warehouse: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse)
    {
        return view('product::warehouse.show', compact('warehouse'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse)
    {
        return view('product::warehouse.edit', compact('warehouse'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'mobile' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        try {
            $warehouse->update($request->all());
            return redirect()->route('admin.warehouseIndex')
                           ->with('success', 'Warehouse updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Failed to update warehouse: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        try {
            $warehouse->delete();
            return redirect()->route('admin.warehouseIndex')
                           ->with('success', 'Warehouse deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Failed to delete warehouse: ' . $e->getMessage());
        }
    }
}