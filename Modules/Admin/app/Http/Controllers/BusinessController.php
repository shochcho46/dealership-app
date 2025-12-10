<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Entities\Business;

class BusinessController extends Controller
{
    /**
     * Display the business settings (redirects to create or edit)
     */
    public function index()
    {
        $business = Business::first();

        if ($business) {
            return redirect()->route('admin.businessEdit', $business);
        }

        return redirect()->route('admin.businessCreate');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check if business already exists
        $business = Business::first();
        if ($business) {
            return redirect()->route('admin.businessEdit', $business);
        }

        return view('admin::business.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if business already exists
        $existing = Business::first();
        if ($existing) {
            return redirect()->route('admin.businessEdit', $existing)
                ->with('error', 'Business settings already exist. Please edit instead.');
        }

        $request->validate([
            'company_name' => 'nullable|string|max:255',
            'mobile_one' => 'nullable|string|max:20',
            'mobile_two' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'brand_name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $business = Business::create([
                'company_name' => $request->company_name,
                'mobile_one' => $request->mobile_one,
                'mobile_two' => $request->mobile_two,
                'email' => $request->email,
                'address' => $request->address,
                'brand_name' => $request->brand_name,
            ]);

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $business->addMediaFromRequest('logo')
                    ->toMediaCollection('logo');
            }

            DB::commit();

            return redirect()->route('admin.businessEdit', $business)
                ->with('success', 'Business settings created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create business settings. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Business $business)
    {
        return view('admin::business.edit', compact('business'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Business $business)
    {
        $request->validate([
            'company_name' => 'nullable|string|max:255',
            'mobile_one' => 'nullable|string|max:20',
            'mobile_two' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'brand_name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $business->update([
                'company_name' => $request->company_name,
                'mobile_one' => $request->mobile_one,
                'mobile_two' => $request->mobile_two,
                'email' => $request->email,
                'address' => $request->address,
                'brand_name' => $request->brand_name,
            ]);

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Clear old logo
                $business->clearMediaCollection('logo');

                // Add new logo
                $business->addMediaFromRequest('logo')
                    ->toMediaCollection('logo');
            }

            DB::commit();

            return back()->with('success', 'Business settings updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update business settings. Please try again.');
        }
    }

    /**
     * Remove logo
     */
    public function deleteLogo(Business $business)
    {
        try {
            $business->clearMediaCollection('logo');
            return back()->with('success', 'Logo deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete logo. Please try again.');
        }
    }
}
