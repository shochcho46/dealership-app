<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Product\Models\VendorAccount;
use Modules\Product\Models\Vendor;
use Modules\Product\Models\PaymentMethod;
use Modules\Product\Models\Order;
use App\Models\Admin;

class PaymentCollectionController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 50);
        $query = VendorAccount::with(['vendor', 'order', 'paymentMethod', 'createdBy', 'depositeBy']);

        // Only filter by credit type (2) if no type filter is applied
        if (!$request->filled('type_filter')) {
            // $query->where('type', 2);
        } else {
            $query->where('type', $request->type_filter);
        }

        // Date range defaults to today when not given
        $dateFrom = $request->filled('date_from') ? $request->date_from : today()->toDateString();
        $dateTo = $request->filled('date_to') ? $request->date_to : today()->toDateString();

        // Shared filters (type filter is excluded so summary covers both types)
        $applyFilters = function ($q) use ($request, $dateFrom, $dateTo) {
            if ($request->filled('vendor_filter')) {
                $q->where('vendor_id', $request->vendor_filter);
            }

            if ($request->filled('payment_method_filter')) {
                $q->where('payment_method_id', $request->payment_method_filter);
            }

            if ($request->filled('deposited_by_filter')) {
                $q->where('deposite_by', $request->deposited_by_filter);
            }

            // Debit rows (created from orders) have no collection_date, so fall back to created_at
            $q->whereRaw('COALESCE(collection_date, DATE(created_at)) >= ?', [$dateFrom])
                ->whereRaw('COALESCE(collection_date, DATE(created_at)) <= ?', [$dateTo]);

            return $q;
        };

        $applyFilters($query);

        $collections = $query->orderBy('id', 'desc')->paginate($limit)->appends($request->query());

        $totalCollected = $applyFilters(VendorAccount::query())->where('type', 2)->sum('amount');
        $totalPending = $applyFilters(VendorAccount::query())->where('type', 1)->sum('amount');

        $pendingCollect =  $totalPending - $totalCollected;
        $vendors = Vendor::orderBy('shop_name')->get();
        $paymentMethods = PaymentMethod::orderBy('id')->get();
        $admins = Admin::where('status', 1)->orderBy('name')->get();

        return view('product::payment_collection.index', compact('collections', 'totalCollected', 'totalPending', 'pendingCollect', 'vendors', 'paymentMethods', 'admins', 'dateFrom', 'dateTo'));
    }

    public function create(Request $request)
    {
        $vendor = null;
        $order = null;
        $vendor = Vendor::query();
        if ($request->filled('vendor_id')) {
            $vendor = Vendor::find($request->vendor_id);
        } else {
            $vendor = Vendor::get();
        }

        if ($request->filled('order_id')) {
            $order = Order::find($request->order_id);
            if ($order && !$vendor) {
                $vendor = $order->vendor;
            }
        }

        $paymentMethods = PaymentMethod::orderBy('id')->get();
        $admins = Admin::where('status', 1)->orderBy('name')->get();

        $defaultPaymentMethodId = $paymentMethods
            ->first(fn ($method) => strtolower(trim($method->account_name)) === 'cash')?->id;

        return view('product::payment_collection.create', compact('vendor', 'order', 'paymentMethods', 'admins', 'defaultPaymentMethodId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'collection_date' => 'required|date',
            'deposite_by' => 'required|exists:admins,id',
            'note' => 'nullable|string|max:1000',
            'payment_document' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,webp|max:5120',
            'allocations' => 'required|array|min:1',
            'allocations.*' => 'required|numeric|min:0.01',
        ], [
            'allocations.required' => 'Please select at least one invoice and enter its amount.',
            'allocations.*.required' => 'Please enter an amount for every selected invoice.',
            'allocations.*.min' => 'Each invoice amount must be greater than zero.',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $vendor = Vendor::findOrFail($request->vendor_id);
                $allocations = collect($request->allocations)
                    ->mapWithKeys(fn ($amount, $orderId) => [(int) $orderId => round((float) $amount, 2)]);

                $orders = Order::where('vendor_id', $vendor->id)
                    ->where('payment_status', '!=', 2)
                    ->whereIn('id', $allocations->keys())
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get();

                if ($orders->count() !== $allocations->count()) {
                    throw new \Exception('One or more selected invoices are invalid or already fully paid.');
                }

                $processedOrders = [];
                $createdPayments = [];
                $totalCollected = 0;

                foreach ($orders as $order) {
                    $paymentForThisOrder = $allocations[$order->id];
                    $orderTotalAmount = (float) $order->total_amount;
                    $alreadyPaid = (float) ($order->paid_amount ?? 0);
                    $orderRemainingAmount = round($orderTotalAmount - $alreadyPaid, 2);

                    if ($paymentForThisOrder > $orderRemainingAmount) {
                        throw new \Exception("Amount for invoice {$order->invoice_id} (৳" . number_format($paymentForThisOrder, 2) . ") exceeds its due (৳" . number_format($orderRemainingAmount, 2) . ").");
                    }

                    $payment = VendorAccount::create([
                        'vendor_id' => $vendor->id,
                        'order_id' => $order->id,
                        'payment_method_id' => $request->payment_method_id,
                        'amount' => $paymentForThisOrder,
                        'type' => 2,
                        'note' => $request->note,
                        'collection_date' => $request->collection_date,
                        'deposite_by' => $request->deposite_by
                    ]);

                    $createdPayments[] = $payment;
                    $totalCollected += $paymentForThisOrder;

                    // Update order paid_amount and payment_status
                    $newPaidAmount = round($alreadyPaid + $paymentForThisOrder, 2);
                    $order->paid_amount = $newPaidAmount;

                    if ($newPaidAmount >= $orderTotalAmount) {
                        $order->payment_status = 2; // Fully Paid
                        $order->paid_at = now();
                    } else {
                        $order->payment_status = 1; // Partially Paid
                        $order->paid_at = null;
                    }

                    $order->save();

                    $processedOrders[] = $order->invoice_id;
                }

                // Attach document to all created payments
                if ($request->hasFile('payment_document')) {
                    foreach ($createdPayments as $payment) {
                        $payment->addMediaFromRequest('payment_document')
                            ->toMediaCollection('payment_document');
                    }
                }

                return redirect()->route('payment-collections.index')->with('success', "Payment of ৳" . number_format($totalCollected, 2) . " collected successfully from {$vendor->shop_name}. Orders processed: " . implode(', ', $processedOrders));
            });

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Payment collection failed: ' . $e->getMessage());
        }
    }

    public function searchVendors(Request $request)
    {
        $search = $request->get('q', '');
        $id = $request->get('id', null);

        $query = Vendor::query();

        if ($id) {
            $query->where('id', $id);
        } else {
            $query->where(function ($q) use ($search) {
                $q->where('shop_name', 'LIKE', "%{$search}%")->orWhere('mobile', 'LIKE', "%{$search}%")->orWhere('full_address', 'LIKE', "%{$search}%");
            });
        }

        $vendors = $query->limit(10)->get(['id', 'shop_name', 'mobile', 'full_address', 'contact_person']);

        return response()->json($vendors);
    }

    public function getVendorPendingOrders(Request $request)
    {
        $vendorId = $request->get('vendor_id');

        if (!$vendorId) {
            return response()->json([]);
        }

        $orders = Order::where('vendor_id', $vendorId)
            ->whereIn('payment_status', [0, 1])
            ->whereIn('order_status_id', [4, 5])
            ->orderBy('created_at', 'asc')
            ->get(['id', 'invoice_id', 'total_amount', 'paid_amount', 'payment_status', 'created_at']);

        return response()->json($orders);
    }

    public function show(VendorAccount $paymentCollection)
    {
        $paymentCollection->load(['vendor', 'order', 'paymentMethod', 'createdBy', 'depositeBy']);

        return view('product::payment_collection.show', compact('paymentCollection'));
    }

    public function destroy(VendorAccount $paymentCollection)
    {
        try {
            return DB::transaction(function() use ($paymentCollection) {
                // Get the order if exists
                $order = $paymentCollection->order;

                // Store amount before deletion
                $deletedAmount = $paymentCollection->amount;

                // Delete the payment collection record
                $paymentCollection->delete();

                // Recalculate order payment status if order exists
                if ($order) {
                    // Update paid_amount by subtracting deleted amount
                    $order->paid_amount = max(0, ($order->paid_amount ?? 0) - $deletedAmount);

                    // Ensure paid_amount doesn't exceed total_amount
                    if ($order->paid_amount > $order->total_amount) {
                        $order->paid_amount = $order->total_amount;
                    }

                    // Update payment status based on paid_amount
                    if ($order->paid_amount <= 0) {
                        $order->payment_status = 0; // Unpaid
                    } elseif ($order->paid_amount >= $order->total_amount) {
                        $order->payment_status = 2; // Fully Paid
                    } else {
                        $order->payment_status = 1; // Partially Paid
                    }

                    $order->save();
                }

                return redirect()->route('payment-collections.index')
                    ->with('success', 'Payment collection deleted successfully. Order payment status updated.');
            });

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete payment collection: ' . $e->getMessage());
        }
    }
}
