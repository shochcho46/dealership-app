<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Unit;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::latest()->get();
        return view('product::unit.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('product::unit.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'status' => 'required|boolean'
        ], [
            'name.required' => 'Unit name is required.',
            'name.unique' => 'This unit name already exists.',
            'status.required' => 'Status is required.'
        ]);

        try {
            Unit::create([
                'name' => $request->name,
                'status' => $request->status
            ]);

            return redirect()->route('admin.unitIndex')->with('success', 'Unit created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create unit. Please try again.')->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit)
    {
        return view('product::unit.edit', compact('unit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        // Validation
        $request->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
            'status' => 'required|boolean'
        ], [
            'name.required' => 'Unit name is required.',
            'name.unique' => 'This unit name already exists.',
            'status.required' => 'Status is required.'
        ]);

        try {
            $unit->update([
                'name' => $request->name,
                'status' => $request->status
            ]);

            return redirect()->route('admin.unitIndex')->with('success', 'Unit updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update unit. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        try {
            $unit->delete();
            return redirect()->route('admin.unitIndex')->with('success', 'Unit deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete unit. Please try again.');
        }
    }
}
