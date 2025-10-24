<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Color;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $colors = Color::latest()->get();
        return view('product::color.index', compact('colors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('product::color.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'name' => 'required|string|max:255|unique:colors,name',
            'code' => 'required|string|size:7|regex:/^#[a-fA-F0-9]{6}$/|unique:colors,code',
            'status' => 'required|boolean'
        ], [
            'name.required' => 'Color name is required.',
            'name.unique' => 'This color name already exists.',
            'code.required' => 'Color code is required.',
            'code.size' => 'Color code must be exactly 7 characters (including #).',
            'code.regex' => 'Color code must be a valid hex color (e.g., #ffffff).',
            'code.unique' => 'This color code already exists.',
            'status.required' => 'Status is required.'
        ]);

        try {
            Color::create([
                'name' => $request->name,
                'code' => $request->code,
                'status' => $request->status
            ]);

            return redirect()->route('admin.colorIndex')->with('success', 'Color created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create color. Please try again.')->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Color $color)
    {
        return view('product::color.edit', compact('color'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Color $color)
    {
        // Validation
        $request->validate([
            'name' => 'required|string|max:255|unique:colors,name,' . $color->id,
            'code' => 'required|string|size:7|regex:/^#[a-fA-F0-9]{6}$/|unique:colors,code,' . $color->id,
            'status' => 'required|boolean'
        ], [
            'name.required' => 'Color name is required.',
            'name.unique' => 'This color name already exists.',
            'code.required' => 'Color code is required.',
            'code.size' => 'Color code must be exactly 7 characters (including #).',
            'code.regex' => 'Color code must be a valid hex color (e.g., #ffffff).',
            'code.unique' => 'This color code already exists.',
            'status.required' => 'Status is required.'
        ]);

        try {
            $color->update([
                'name' => $request->name,
                'code' => $request->code,
                'status' => $request->status
            ]);

            return redirect()->route('admin.colorIndex')->with('success', 'Color updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update color. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Color $color)
    {
        try {
            $color->delete();
            return redirect()->route('admin.colorIndex')->with('success', 'Color deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete color. Please try again.');
        }
    }
}
