<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Product\Models\DsrCollection;
use Modules\Product\Models\Vendor;
use Modules\Product\Models\PaymentMethod;

class DsrCollectionController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 50);
        $query = DsrCollection::with(['vendor', 'paymentMethod', 'createdBy', 'depositeBy']);

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('payment_method_filter')) {
            $query->where('payment_method_id', $request->payment_method_filter);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('collection_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('collection_date', '<=', $request->date_to);
        }

        $collections = $query->orderBy('id', 'desc')->paginate($limit)->appends($request->query());

        // Total for the current filtered result set
        $filteredQuery = DsrCollection::query();
        if ($request->filled('vendor_id')) {
            $filteredQuery->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('payment_method_filter')) {
            $filteredQuery->where('payment_method_id', $request->payment_method_filter);
        }
        if ($request->filled('date_from')) {
            $filteredQuery->whereDate('collection_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $filteredQuery->whereDate('collection_date', '<=', $request->date_to);
        }
        $filteredTotal = $filteredQuery->sum('amount');

        $totalAll       = DsrCollection::sum('amount');
        $paymentMethods = PaymentMethod::orderBy('id')->get();

        // Pre-load selected vendor for the search input display
        $selectedVendor = null;
        if ($request->filled('vendor_id')) {
            $selectedVendor = Vendor::find($request->vendor_id);
        }

        return view('product::dsr_collection.index', compact(
            'collections', 'totalAll', 'filteredTotal', 'paymentMethods', 'selectedVendor'
        ));
    }

    public function create()
    {
        $paymentMethods = PaymentMethod::orderBy('id')->get();
        $currentAdmin   = auth()->guard('admin')->user();
        $canEditDate    = $currentAdmin->hasAnyRole(['SuperAdmin', 'admin']);

        return view('product::dsr_collection.create', compact('paymentMethods', 'currentAdmin', 'canEditDate'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id'         => 'required|exists:vendors,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount'            => 'required|numeric|min:0.01',
            'collection_date'   => 'required|date',
            'note'              => 'nullable|string|max:1000',
        ]);

        // Always use the currently authenticated admin as the depositor
        $depositeBy = auth()->guard('admin')->id();

        try {
            $collection = DsrCollection::create([
                'vendor_id'         => $request->vendor_id,
                'payment_method_id' => $request->payment_method_id,
                'amount'            => $request->amount,
                'collection_date'   => $request->collection_date,
                'note'              => $request->note,
                'deposite_by'       => $depositeBy,
            ]);

            $vendor = Vendor::find($request->vendor_id);
            if ($vendor->mobile && $vendor->mobile != NULL && config('app.collection_sms') == 1) {
                $message = date('d-m-Y')
                    . " জমা: ৳" . number_format($request->amount, 2)
                    . "\nমোট বকেয়া: ৳" . number_format($vendor->due_balance, 2)
                    . "\n\n- এস এস এন্টারপ্রাইজ";
            // Send SMS notification (non-blocking)
                try {
                    Http::asForm()->post('https://api.bdbulksms.net/api.php?json', [
                        'to' => $vendor->mobile,
                        'message' => $message,
                        'token' => 'c3253885b10f98c971b719b5372a4b34'
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't halt registration process
                    Log::error('SMS sending failed: ' . $e->getMessage());
                }
            }
            return redirect()->route('dsr-collections.index')
                ->with('success', "Collection of ৳" . number_format($request->amount, 2) . " recorded successfully from {$vendor->shop_name}.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to record collection: ' . $e->getMessage());
        }
    }

    public function show(DsrCollection $dsrCollection)
    {
        $dsrCollection->load(['vendor', 'paymentMethod', 'createdBy', 'depositeBy']);

        return view('product::dsr_collection.show', compact('dsrCollection'));
    }

    public function destroy(DsrCollection $dsrCollection)
    {
        try {
            $dsrCollection->delete();

            return redirect()->route('dsr-collections.index')
                ->with('success', 'Collection record deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete collection: ' . $e->getMessage());
        }
    }

    public function searchVendors(Request $request)
    {
        $search = $request->get('q', '');
        $id     = $request->get('id', null);

        $query = Vendor::query();

        if ($id) {
            $query->where('id', $id);
        } else {
            $query->where(function ($q) use ($search) {
                $q->where('shop_name', 'LIKE', "%{$search}%")
                  ->orWhere('mobile', 'LIKE', "%{$search}%");
            });
        }

        $vendors = $query->limit(10)->get(['id', 'shop_name', 'mobile', 'full_address', 'contact_person']);

        return response()->json($vendors);
    }
}
