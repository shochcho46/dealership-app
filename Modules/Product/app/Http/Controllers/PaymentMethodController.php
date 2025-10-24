<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentMethods = PaymentMethod::latest()->paginate(10);
        return view('product::paymentmethod.index', compact('paymentMethods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('product::paymentmethod.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'institute_name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        try {
            PaymentMethod::create($request->all());
            return redirect()->route('admin.paymentMethodIndex')
                           ->with('success', 'Payment method created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Failed to create payment method: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentMethod $paymentMethod)
    {
        return view('product::paymentmethod.show', compact('paymentMethod'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        return view('product::paymentmethod.edit', compact('paymentMethod'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'institute_name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        try {
            $paymentMethod->update($request->all());
            return redirect()->route('admin.paymentMethodIndex')
                           ->with('success', 'Payment method updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Failed to update payment method: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        try {
            $paymentMethod->delete();
            return redirect()->route('admin.paymentMethodIndex')
                           ->with('success', 'Payment method deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Failed to delete payment method: ' . $e->getMessage());
        }
    }
}