<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Models\Company;
use Modules\Product\Models\CompanyOrder;
use Modules\Product\Models\CompanyOrderItem;
use Modules\Product\Models\CompanyOrderPayment;
use Modules\Product\Models\PaymentMethod;
use Modules\Product\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;

class CompanyOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 30);
        $query = CompanyOrder::with(['company', 'items']);

        // Filter by company
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by order status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->latest()->paginate($limit);
        $companies = Company::where('status', 1)->get();
        $paymentMethods = PaymentMethod::where('status', 1)->get();
        return view('product::company-order.index', compact('orders', 'companies', 'paymentMethods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::where('status', 1)->get();
        $paymentMethods = PaymentMethod::where('status', 1)->get();
        return view('product::company-order.create', compact('companies', 'paymentMethods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Generate order number
            $lastOrder = CompanyOrder::latest('id')->first();
            $orderNumber = 'CO-' . str_pad(($lastOrder ? $lastOrder->id + 1 : 1), 6, '0', STR_PAD_LEFT);

            // Calculate total
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }

            // Create order
            $order = CompanyOrder::create([
                'company_id' => $request->company_id,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'payment_status' => 'unpaid',
                'notes' => $request->notes,
            ]);

            // Create order items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                CompanyOrderItem::create([
                    'company_order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'measurement_unit' => $product->measurement_unit_name . ' (' . $product->measurement_unit_number . ')',
                    'package_unit' => $product->package_unit_name . ' (' . $product->package_unit_quantity . ')',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total_price' => $item['quantity'] * $item['price'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.companyOrderIndex')->with('success', 'Order created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create order. Please try again.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CompanyOrder $companyOrder)
    {
        $companyOrder->load(['company', 'items.product', 'payments.paymentMethod', 'payments.creator']);
        $paymentMethods = PaymentMethod::where('status', 1)->get();
        return view('product::company-order.show', compact('companyOrder', 'paymentMethods'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompanyOrder $companyOrder)
    {
        $companyOrder->load(['items']);
        $companies = Company::where('status', 1)->get();
        $paymentMethods = PaymentMethod::where('status', 1)->get();
        return view('product::company-order.edit', compact('companyOrder', 'companies', 'paymentMethods'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CompanyOrder $companyOrder)
    {
        // Validation
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }

            // Update order
            $companyOrder->update([
                'company_id' => $request->company_id,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
            ]);

            // Update payment status
            if ($companyOrder->paid_amount >= $totalAmount) {
                $companyOrder->update(['payment_status' => 'paid']);
            } elseif ($companyOrder->paid_amount > 0) {
                $companyOrder->update(['payment_status' => 'partial']);
            } else {
                $companyOrder->update(['payment_status' => 'unpaid']);
            }

            // Delete old items
            $companyOrder->items()->delete();

            // Create new items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                CompanyOrderItem::create([
                    'company_order_id' => $companyOrder->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'measurement_unit' => $product->measurement_unit_name . ' (' . $product->measurement_unit_number . ')',
                    'package_unit' => $product->package_unit_name . ' (' . $product->package_unit_quantity . ')',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total_price' => $item['quantity'] * $item['price'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.companyOrderIndex')->with('success', 'Order updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update order. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompanyOrder $companyOrder)
    {
        try {
            $companyOrder->delete();
            return redirect()->route('admin.companyOrderIndex')->with('success', 'Order deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete order. Please try again.');
        }
    }

    /**
     * Get products by company (AJAX)
     */
    public function getProductsByCompany($companyId)
    {
        try {
            $products = Product::where('company_id', $companyId)
                ->where('status', 1)
                ->get(['id', 'name', 'measurement_unit_name', 'measurement_unit_number', 'package_unit_name', 'package_unit_quantity']);

            return response()->json(['success' => true, 'products' => $products]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch products.'], 500);
        }
    }

    /**
     * Add payment to order
     */
    public function addPayment(Request $request, CompanyOrder $companyOrder)
    {
        // Validation
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'payment_slip' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ]);

        try {
            DB::beginTransaction();

            // Check if payment exceeds remaining amount
            $remainingAmount = $companyOrder->total_amount - $companyOrder->paid_amount;
            if ($request->amount > $remainingAmount) {
                return back()->with('error', 'Payment amount exceeds remaining balance.');
            }

            // Create payment
            $payment = CompanyOrderPayment::create([
                'company_order_id' => $companyOrder->id,
                'payment_method_id' => $request->payment_method_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date ?? now(),
                'notes' => $request->notes,
            ]);

            // Upload payment slip if provided
            if ($request->hasFile('payment_slip')) {
                $payment->addMedia($request->file('payment_slip'))
                    ->toMediaCollection('payment_slip');
            }

            // Update order paid amount
            $newPaidAmount = $companyOrder->paid_amount + $request->amount;
            $companyOrder->update(['paid_amount' => $newPaidAmount]);

            // Update payment status
            if ($newPaidAmount >= $companyOrder->total_amount) {
                $companyOrder->update(['payment_status' => 'paid']);
            } elseif ($newPaidAmount > 0) {
                $companyOrder->update(['payment_status' => 'partial']);
            }

            DB::commit();

            return back()->with('success', 'Payment added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to add payment. Please try again.');
        }
    }

    /**
     * Update payment
     */
    public function updatePayment(Request $request, CompanyOrder $companyOrder, CompanyOrderPayment $payment)
    {
        // Validation
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'payment_slip' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ]);

        try {
            DB::beginTransaction();

            // Calculate remaining amount excluding current payment
            $remainingAmount = $companyOrder->total_amount - ($companyOrder->paid_amount - $payment->amount);
            if ($request->amount > $remainingAmount) {
                return back()->with('error', 'Payment amount exceeds remaining balance.');
            }

            // Update payment
            $payment->update([
                'payment_method_id' => $request->payment_method_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date ?? $payment->payment_date,
                'notes' => $request->notes,
            ]);

            // Upload new payment slip if provided
            if ($request->hasFile('payment_slip')) {
                $payment->clearMediaCollection('payment_slip');
                $payment->addMedia($request->file('payment_slip'))
                    ->toMediaCollection('payment_slip');
            }

            // Recalculate order paid amount
            $newPaidAmount = $companyOrder->payments()->sum('amount');
            $companyOrder->update(['paid_amount' => $newPaidAmount]);

            // Update payment status
            if ($newPaidAmount >= $companyOrder->total_amount) {
                $companyOrder->update(['payment_status' => 'paid']);
            } elseif ($newPaidAmount > 0) {
                $companyOrder->update(['payment_status' => 'partial']);
            } else {
                $companyOrder->update(['payment_status' => 'unpaid']);
            }

            DB::commit();

            return back()->with('success', 'Payment updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update payment. Please try again.');
        }
    }

    /**
     * Delete payment
     */
    public function deletePayment(CompanyOrder $companyOrder, CompanyOrderPayment $payment)
    {
        try {
            DB::beginTransaction();

            $payment->delete();

            // Recalculate order paid amount
            $newPaidAmount = $companyOrder->payments()->sum('amount');
            $companyOrder->update(['paid_amount' => $newPaidAmount]);

            // Update payment status
            if ($newPaidAmount >= $companyOrder->total_amount) {
                $companyOrder->update(['payment_status' => 'paid']);
            } elseif ($newPaidAmount > 0) {
                $companyOrder->update(['payment_status' => 'partial']);
            } else {
                $companyOrder->update(['payment_status' => 'unpaid']);
            }

            DB::commit();

            return back()->with('success', 'Payment deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete payment. Please try again.');
        }
    }

    /**
     * Update item damage and lost quantities
     */
    public function updateItemDamageLost(Request $request, CompanyOrder $companyOrder, CompanyOrderItem $item)
    {
        $request->validate([
            'damage_quantity' => 'required|integer|min:0',
            'lost_quantity' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Calculate damage and lost prices
            $damagePrice = $request->damage_quantity * $item->price;
            $lostPrice = $request->lost_quantity * $item->price;

            $item->update([
                'damage_quantity' => $request->damage_quantity,
                'lost_quantity' => $request->lost_quantity,
                'damage_price' => $damagePrice,
                'lost_price' => $lostPrice,
            ]);

            // Recalculate total_price considering damage and lost
            // Total = (quantity - damage - lost) * price + damage_price + lost_price
            $effectiveQuantity = $item->quantity - $request->damage_quantity - $request->lost_quantity;
            $newTotalPrice = ($effectiveQuantity * $item->price) + $damagePrice + $lostPrice;
            $item->update(['total_price' => $newTotalPrice]);

            // Recalculate order total amount
            $newTotalAmount = $companyOrder->items()->sum('total_price');
            $companyOrder->update(['total_amount' => $newTotalAmount]);

            // Update payment status based on new total
            if ($companyOrder->paid_amount >= $newTotalAmount) {
                $companyOrder->update(['payment_status' => 'paid']);
            } elseif ($companyOrder->paid_amount > 0) {
                $companyOrder->update(['payment_status' => 'partial']);
            } else {
                $companyOrder->update(['payment_status' => 'unpaid']);
            }

            DB::commit();

            return back()->with('success', 'Damage and lost quantities updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update. Please try again.');
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, CompanyOrder $companyOrder)
    {
        $request->validate([
            'status' => 'required|in:pending,received',
        ]);

        try {
            $companyOrder->update(['status' => $request->status]);
            return back()->with('success', 'Order status updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update status. Please try again.');
        }
    }

    /**
     * Generate PDF for order
     */
    public function generatePdf(CompanyOrder $companyOrder)
    {
        $companyOrder->load(['company', 'items']);

        $pdf = Pdf::loadView('product::company-order.pdf', compact('companyOrder'));

        return $pdf->stream('order-' . $companyOrder->order_number . '.pdf');
    }
}
