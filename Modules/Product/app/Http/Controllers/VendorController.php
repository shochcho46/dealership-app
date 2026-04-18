<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Modules\Product\Models\Vendor;
use Modules\Product\Models\VendorAccount;
use Modules\Product\Models\Order;
use App\Models\Country;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Entities\Business;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $limit = request()->get('limit', 50);
        $search = request()->get('search');
        $query = Vendor::with('country')
            ->withSum(
                ['vendorAccounts as total_debit' => function ($q) {
                    $q->where('type', 1);
                }],
                'amount'
            )->withSum(
                ['vendorAccounts as total_credit' => function ($q) {
                    $q->where('type', 2);
                }],
                'amount'
            )

        ->when($search, function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('shop_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            });
        });


        // 🔹 Clone full query for overall totals
        $fullQuery = clone $query;

        // 🔹 Paginated result
        $vendors = $query->orderBy('id', 'desc')->paginate($limit);

        /* ===============================
            OVERALL TOTAL (ALL RECORDS)
        =============================== */
        $overallDueBalance = $fullQuery->get()->sum(function ($vendor) {
            return ($vendor->total_debit ?? 0) - ($vendor->total_credit ?? 0);
        });

        /* ===============================
            CURRENT PAGE TOTAL
        =============================== */
        $pageDueBalance = $vendors->getCollection()->sum(function ($vendor) {
            return ($vendor->total_debit ?? 0) - ($vendor->total_credit ?? 0);
        });

        return view('product::vendor.index', compact(
            'vendors',
            'overallDueBalance',
            'pageDueBalance'
        ));
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
        $limit = $request->get('limit', 50);
        $vendor = Vendor::where('uuid', $uuid)->firstOrFail();

        // Get date range from request or default to current month
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfMonth()->endOfDay();

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
        $accounts =  $accountsQuery->paginate($limit);
        $admins = Admin::role(['admin', 'subadmin', 'dsr', 'sr'])->orderBy('name')->get();
        $startDate = $startDate->format('Y-m-d');
        $endDate = $endDate->format('Y-m-d');
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
            'endDate',
            'admins',
        ));
    }

    /**
     * Display vendor account/financial records (Public - For Vendor Access)
     */
    public function vendorPublicAccount(Request $request, $uuid)
    {
        $limit = $request->get('limit', 50);
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
        $accounts =  $accountsQuery->paginate($limit);

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
        // Validation
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'vendor_id' => 'required',
            'type' => 'required|in:1,2',
            'deposite_by' => 'required|exists:admins,id',
        ]);

        $dateOfcollection = null;
        if ($request->type == 2 ) {
            $dateOfcollection = Carbon::now()->format('Y-m-d');
        }

        try {
            $investment = VendorAccount::create([
                'vendor_id' => $request->vendor_id,
                'amount' => $request->amount,
                'type' => $request->type,
                'deposite_by' => $request->deposite_by,
                'collection_date' => $dateOfcollection,
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
            DB::transaction(function () use ($vendorAccount) {
                $order = $vendorAccount->order;
                if ($order) {
                    // Remove the amount from paid_amount
                    $order->paid_amount = max(0, $order?->paid_amount - $vendorAccount?->amount);

                    // Calculate due
                    $due = ($order->total_amount - $order->total_discount_amount) - $order->paid_amount;

                    // Update payment status
                    if ($order->paid_amount <= 0) {
                        $order->payment_status = 0; // Unpaid
                        $order->paid_at = null;
                    } elseif ($due > 0) {
                        $order->payment_status = 1; // Partial
                        $order->paid_at = null;
                    } else {
                        $order->payment_status = 2; // Paid
                        $order->paid_at = now();
                    }

                    $order->save();
                }
                $vendorAccount->delete();
            });
            return back()->with('success', 'Account deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete account. Please try again.');
        }
    }

    /**
     * Vendor Analysis Report - Compare date ranges with vendor performance
     */
    public function vendorAnalysis(Request $request)
    {
        $limit = $request->get('limit', 50);

        // Set default date range - last 7 days if no dates provided
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::parse($endDate)->subDays(6)->startOfDay();

        // Calculate previous period (same length) - round to whole number
        $daysDiff = round($startDate->diffInDays($endDate) + 1);
        $previousStartDate = Carbon::parse($startDate)->subDays($daysDiff)->startOfDay();
        $previousEndDate = Carbon::parse($startDate)->subDay()->endOfDay();

        // Build base query for vendors
        $vendorQuery = Vendor::query()->with('country');

        // Filter by vendor (multiple selection)
        if ($request->filled('vendor_id')) {
            $vendorIds = is_array($request->vendor_id) ? $request->vendor_id : [$request->vendor_id];
            $vendorQuery->whereIn('id', $vendorIds);
        }

        // Get vendors
        $vendors = $vendorQuery->get();

        // Build query for current period orders
        $currentOrdersQuery = Order::with(['vendor', 'placeBy', 'orderItems'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('order_status_id', '!=', 6); // Exclude cancelled

        // Build query for previous period orders
        $previousOrdersQuery = Order::with(['vendor', 'placeBy', 'orderItems'])
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->where('order_status_id', '!=', 6); // Exclude cancelled

        // Filter by place_by (multiple selection)
        if ($request->filled('place_by')) {
            $placeByIds = is_array($request->place_by) ? $request->place_by : [$request->place_by];
            $currentOrdersQuery->whereIn('place_by', $placeByIds);
            $previousOrdersQuery->whereIn('place_by', $placeByIds);
        }

        // Filter by vendor in orders
        if ($request->filled('vendor_id')) {
            $vendorIds = is_array($request->vendor_id) ? $request->vendor_id : [$request->vendor_id];
            $currentOrdersQuery->whereIn('vendor_id', $vendorIds);
            $previousOrdersQuery->whereIn('vendor_id', $vendorIds);
        }

        $currentOrders = $currentOrdersQuery->get();
        $previousOrders = $previousOrdersQuery->get();

        // Build vendor analysis data
        $vendorAnalysisCollection = $vendors->map(function ($vendor) use ($currentOrders, $previousOrders, $startDate, $endDate, $previousStartDate, $previousEndDate) {
            // Current Period Data
            $currentVendorOrders = $currentOrders->where('vendor_id', $vendor->id);
            $currentOrderCount = $currentVendorOrders->count();
            $currentTotalAmount = $currentVendorOrders->sum('total_amount');

            // Get collections (payments) for current period
            $currentCollected = VendorAccount::where('vendor_id', $vendor->id)
                ->where('type', 2) // Credit/Payment
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount');

            // Calculate current due (orders amount - collected)
            $currentDue = $currentTotalAmount - $currentCollected;

            // Previous Period Data
            $previousVendorOrders = $previousOrders->where('vendor_id', $vendor->id);
            $previousOrderCount = $previousVendorOrders->count();
            $previousTotalAmount = $previousVendorOrders->sum('total_amount');

            // Get collections for previous period
            $previousCollected = VendorAccount::where('vendor_id', $vendor->id)
                ->where('type', 2)
                ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
                ->sum('amount');

            $previousDue = $previousTotalAmount - $previousCollected;

            // Calculate percentage changes
            $orderCountChange = $previousOrderCount > 0
                ? (($currentOrderCount - $previousOrderCount) / $previousOrderCount) * 100
                : ($currentOrderCount > 0 ? 100 : 0);

            $amountChange = $previousTotalAmount > 0
                ? (($currentTotalAmount - $previousTotalAmount) / $previousTotalAmount) * 100
                : ($currentTotalAmount > 0 ? 100 : 0);

            $collectionChange = $previousCollected > 0
                ? (($currentCollected - $previousCollected) / $previousCollected) * 100
                : ($currentCollected > 0 ? 100 : 0);

            $dueChange = $previousDue > 0
                ? (($currentDue - $previousDue) / $previousDue) * 100
                : ($currentDue > 0 ? 100 : 0);

            // All-time statistics
            $allTimeOrders = Order::where('vendor_id', $vendor->id)
                ->where('order_status_id', '!=', 6)
                ->count();

            $allTimeTotalAmount = Order::where('vendor_id', $vendor->id)
                ->where('order_status_id', '!=', 6)
                ->sum('total_amount');

            $allTimeCollected = VendorAccount::where('vendor_id', $vendor->id)
                ->where('type', 2)
                ->sum('amount');

            $allTimeDue = $allTimeTotalAmount - $allTimeCollected;

            // Get place_by breakdown for current period
            $placeByBreakdown = $currentVendorOrders->groupBy('place_by')
                ->map(function ($orders, $placeById) {
                    $admin = Admin::find($placeById);
                    return [
                        'admin' => $admin,
                        'count' => $orders->count(),
                        'amount' => $orders->sum('total_amount'),
                    ];
                })
                ->sortByDesc('count')
                ->values();

            return [
                'vendor' => $vendor,
                'current' => [
                    'order_count' => $currentOrderCount,
                    'total_amount' => $currentTotalAmount,
                    'collected' => $currentCollected,
                    'due' => $currentDue,
                ],
                'previous' => [
                    'order_count' => $previousOrderCount,
                    'total_amount' => $previousTotalAmount,
                    'collected' => $previousCollected,
                    'due' => $previousDue,
                ],
                'changes' => [
                    'order_count' => $orderCountChange,
                    'amount' => $amountChange,
                    'collection' => $collectionChange,
                    'due' => $dueChange,
                ],
                'all_time' => [
                    'order_count' => $allTimeOrders,
                    'total_amount' => $allTimeTotalAmount,
                    'collected' => $allTimeCollected,
                    'due' => $allTimeDue,
                ],
                'place_by_breakdown' => $placeByBreakdown,
            ];
        });

        // Sort by current due amount (descending)
        $vendorAnalysisCollection = $vendorAnalysisCollection->sortByDesc('current.due');

        // Calculate totals for the filtered results
        $totals = [
            'current' => [
                'order_count' => $vendorAnalysisCollection->sum('current.order_count'),
                'total_amount' => $vendorAnalysisCollection->sum('current.total_amount'),
                'collected' => $vendorAnalysisCollection->sum('current.collected'),
                'due' => $vendorAnalysisCollection->sum('current.due'),
            ],
            'previous' => [
                'order_count' => $vendorAnalysisCollection->sum('previous.order_count'),
                'total_amount' => $vendorAnalysisCollection->sum('previous.total_amount'),
                'collected' => $vendorAnalysisCollection->sum('previous.collected'),
                'due' => $vendorAnalysisCollection->sum('previous.due'),
            ],
        ];

        // Paginate the collection
        $currentPage = $request->get('page', 1);
        $vendorAnalysisPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $vendorAnalysisCollection->forPage($currentPage, $limit),
            $vendorAnalysisCollection->count(),
            $limit,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $vendorAnalysis = $vendorAnalysisPaginated;

        // Get filter data
        $allVendors = Vendor::orderBy('shop_name')->get();
        $admins = Admin::role(['admin', 'subadmin', 'dsr', 'sr'])->orderBy('name')->get();

        return view('product::vendor.analysis', compact(
            'vendorAnalysis',
            'totals',
            'allVendors',
            'admins',
            'startDate',
            'endDate',
            'previousStartDate',
            'previousEndDate',
            'daysDiff',
            'limit'
        ));
    }

}
