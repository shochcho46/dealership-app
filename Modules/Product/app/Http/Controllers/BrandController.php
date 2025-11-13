<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Brand;
use Modules\Product\Models\Company;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::with('company')->latest()->get();
        return view('product::brand.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::active()->get();
        return view('product::brand.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'company_id' => 'required|exists:companies,id',
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ], [
            'name.required' => 'Brand name is required.',
            'name.unique' => 'This brand name already exists.',
            'company_id.required' => 'Company is required.',
            'company_id.exists' => 'Selected company is invalid.',
            'status.required' => 'Status is required.'
        ]);

        try {
            Brand::create([
                'name' => $request->name,
                'company_id' => $request->company_id,
                'description' => $request->description,
                'status' => $request->status
            ]);

            return redirect()->route('admin.brandIndex')->with('success', 'Brand created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create brand. Please try again.')->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        $companies = Company::active()->get();
        return view('product::brand.edit', compact('brand', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        // Validation
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'company_id' => 'required|exists:companies,id',
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ], [
            'name.required' => 'Brand name is required.',
            'name.unique' => 'This brand name already exists.',
            'company_id.required' => 'Company is required.',
            'company_id.exists' => 'Selected company is invalid.',
            'status.required' => 'Status is required.'
        ]);

        try {
            $brand->update([
                'name' => $request->name,
                'company_id' => $request->company_id,
                'description' => $request->description,
                'status' => $request->status
            ]);

            return redirect()->route('admin.brandIndex')->with('success', 'Brand updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update brand. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        try {
            $brand->delete();
            return redirect()->route('admin.brandIndex')->with('success', 'Brand deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete brand. Please try again.');
        }
    }
}
