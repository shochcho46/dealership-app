<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Vendor;
use Modules\Product\Models\VendorAccount;
use App\Models\Country;
use Carbon\Carbon;
use Modules\Admin\Entities\Business;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $limit = request()->get('limit', 30);
        $vendors = Vendor::with('country')->latest()->paginate($limit);
        return view('product::vendor.index', compact('vendors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = Country::all();
        return view('product::vendor.create', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
     $request->validate([
            'shop_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'country_id' => 'nullable|exists:countries,id',
            'full_address' => 'nullable|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'long' => 'nullable|numeric|between:-180,180',
            'status' => 'required|boolean',
            'vendor_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
        ], [
            'shop_name.required' => 'Shop name is required.',
            'mobile.required' => 'Mobile number is required.',
            'email.email' => 'Please enter a valid email address.',
            'country_id.exists' => 'Selected country is invalid.',
            'lat.between' => 'Latitude must be between -90 and 90.',
            'long.between' => 'Longitude must be between -180 and 180.',
            'vendor_image.image' => 'Vendor image must be an image file.',
            'vendor_image.max' => 'Vendor image must not be larger than 10MB.',
        ]);


        try {
            $countriesIso = Country::where('id',18)->first();
            $phoneNumber = validationMobileNumber($request->mobile,$countriesIso->iso);
            $vendor = Vendor::create([
                'shop_name' => $request->shop_name,
                'mobile' => $phoneNumber ?? $request->mobile,
                'email' => $request->email,
                'contact_person' => $request->contact_person,
                'country_id' => 18,
                'full_address' => $request->full_address,
                'lat' => $request->lat,
                'long' => $request->long,
                'status' => $request->status
            ]);

            // Handle image upload
            if ($request->hasFile('vendor_image')) {
                $vendor->addMediaFromRequest('vendor_image')
                    ->toMediaCollection('vendor_image');
            }

            return redirect()->route('admin.vendorIndex')->with('success', 'Vendor created successfully!');
        }
        catch (\Exception $e) {
            return back()->with('error', 'Failed to create vendor. Please try again.')->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vendor $vendor)
    {
        $countries = Country::all();
        return view('product::vendor.edit', compact('vendor', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vendor $vendor)
    {
        // Validation
        $request->validate([
            'shop_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'country_id' => 'nullable|exists:countries,id',
            'full_address' => 'nullable|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'long' => 'nullable|numeric|between:-180,180',
            'status' => 'required|boolean',
            'vendor_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
        ], [
            'shop_name.required' => 'Shop name is required.',
            'mobile.required' => 'Mobile number is required.',
            'email.email' => 'Please enter a valid email address.',
            'country_id.exists' => 'Selected country is invalid.',
            'lat.between' => 'Latitude must be between -90 and 90.',
            'long.between' => 'Longitude must be between -180 and 180.',
            'vendor_image.image' => 'Vendor image must be an image file.',
            'vendor_image.max' => 'Vendor image must not be larger than 10MB.',
        ]);

        try {

            $countriesIso = Country::where('id',18)->first();
            $phoneNumber = validationMobileNumber($request->mobile,$countriesIso->iso);
            $vendor->update([
                'shop_name' => $request->shop_name,
                'mobile' => $phoneNumber,
                'email' => $request->email,
                'contact_person' => $request->contact_person,
                'country_id' => 18,
                'full_address' => $request->full_address,
                'lat' => $request->lat,
                'long' => $request->long,
                'status' => $request->status
            ]);

            // Handle image upload
            if ($request->hasFile('vendor_image')) {
                // Clear existing image
                $vendor->clearMediaCollection('vendor_image');

                // Add new image
                $vendor->addMediaFromRequest('vendor_image')
                    ->toMediaCollection('vendor_image');
            }

            return redirect()->route('admin.vendorIndex')->with('success', 'Vendor updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update vendor. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        try {
            // Clear all media
            $vendor->clearMediaCollection('vendor_image');

            $vendor->delete();
            return redirect()->route('admin.vendorIndex')->with('success', 'Vendor deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete vendor. Please try again.');
        }
    }

    /**
     * Display vendor account/financial records (Admin)
     */
    public function account(Request $request, $uuid)
    {
        $vendor = Vendor::where('uuid', $uuid)->firstOrFail();

        // Get date range from request or default to current month
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Build query for vendor accounts with eager loading
        $accountsQuery = VendorAccount::where('vendor_id', $vendor->id)
            ->with(['order', 'paymentMethod', 'depositeBy'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('id', 'desc');

        // Get period accounts
        $resulit = $accountsQuery->get();

        // Calculate period totals
        $totalDebit = $resulit->where('type', 1)->sum('amount');
        $totalCredit = $resulit->where('type', 2)->sum('amount');
        $balance = $totalCredit - $totalDebit;

        // Get all-time data
        $allTimeAccounts = VendorAccount::where('vendor_id', $vendor->id)->get();
        $allTimeDebit = $allTimeAccounts->where('type', 1)->sum('amount');
        $allTimeCredit = $allTimeAccounts->where('type', 2)->sum('amount');
        $allTimeBalance = VendorAccount::getVendorBalance($vendor->id);
        $totalTransactions = $allTimeAccounts->count();
        $accounts =  $accountsQuery->paginate(50);

        return view('product::vendor.account', compact(
            'vendor',
            'accounts',
            'totalDebit',
            'totalCredit',
            'balance',
            'allTimeDebit',
            'allTimeCredit',
            'allTimeBalance',
            'totalTransactions',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display vendor account/financial records (Public - For Vendor Access)
     */
    public function vendorPublicAccount(Request $request, $uuid)
    {
        $vendor = Vendor::where('uuid', $uuid)->firstOrFail();

        // Get date range from request or default to current month
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Build query for vendor accounts with eager loading
        $accountsQuery = VendorAccount::where('vendor_id', $vendor->id)
            ->with(['order', 'paymentMethod', 'depositeBy'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('id', 'desc');

        // Get period accounts
        $resulit = $accountsQuery->get();

        // Calculate period totals
        $totalDebit = $resulit->where('type', 1)->sum('amount');
        $totalCredit = $resulit->where('type', 2)->sum('amount');
        $balance = $totalCredit - $totalDebit;

        // Get all-time data
        $allTimeAccounts = VendorAccount::where('vendor_id', $vendor->id)->get();
        $allTimeDebit = $allTimeAccounts->where('type', 1)->sum('amount');
        $allTimeCredit = $allTimeAccounts->where('type', 2)->sum('amount');
        $allTimeBalance = VendorAccount::getVendorBalance($vendor->id);
        $totalTransactions = $allTimeAccounts->count();
        $businessDetail = Business::first();
        $accounts =  $accountsQuery->paginate(50);

        return view('product::vendor.public-account', compact(
            'vendor',
            'accounts',
            'totalDebit',
            'totalCredit',
            'balance',
            'allTimeDebit',
            'allTimeCredit',
            'allTimeBalance',
            'totalTransactions',
            'startDate',
            'endDate',
            'businessDetail'
        ));
    }


     public function storeVendorAccount(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'vendor_id' => 'required',
            'type' => 'required|in:1,2',
        ]);

        try {
            $investment = VendorAccount::create([
                'vendor_id' => $request->vendor_id,
                'amount' => $request->amount,
                'type' => $request->type
            ]);
            return back()->with('success', 'Investment added successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add investment. Please try again.')->withInput();
        }
    }


     public function destroyVendorAccount(VendorAccount $vendorAccount)
    {
        try {
            // Clear all media
            $vendorAccount->delete();
            return back()->with('success', 'Account deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete account. Please try again.');
        }
    }
}
