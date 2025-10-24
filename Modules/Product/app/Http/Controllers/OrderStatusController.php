<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\OrderStatus;

class OrderStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orderStatuses = OrderStatus::latest()->get();
        return view('product::order-status.index', compact('orderStatuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('product::order-status.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'name' => 'required|string|max:255|unique:order_statuses,name',
            'status' => 'required|boolean'
        ], [
            'name.required' => 'Order status name is required.',
            'name.unique' => 'This order status name already exists.',
            'status.required' => 'Status is required.'
        ]);

        try {
            OrderStatus::create([
                'name' => $request->name,
                'status' => $request->status
            ]);

            return redirect()->route('admin.orderStatusIndex')->with('success', 'Order status created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create order status. Please try again.')->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrderStatus $orderStatus)
    {
        return view('product::order-status.edit', compact('orderStatus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrderStatus $orderStatus)
    {
        // Validation
        $request->validate([
            'name' => 'required|string|max:255|unique:order_statuses,name,' . $orderStatus->id,
            'status' => 'required|boolean'
        ], [
            'name.required' => 'Order status name is required.',
            'name.unique' => 'This order status name already exists.',
            'status.required' => 'Status is required.'
        ]);

        try {
            $orderStatus->update([
                'name' => $request->name,
                'status' => $request->status
            ]);

            return redirect()->route('admin.orderStatusIndex')->with('success', 'Order status updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update order status. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderStatus $orderStatus)
    {
        try {
            $orderStatus->delete();
            return redirect()->route('admin.orderStatusIndex')->with('success', 'Order status deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete order status. Please try again.');
        }
    }
}
