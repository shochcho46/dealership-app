<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\ExpenseHead;

class ExpenseHeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $limit = request()->get('limit', 30);
        $expenseHeads = ExpenseHead::withCount('expenseLists')->latest()->paginate($limit);
        return view('product::expense-head.index', compact('expenseHeads'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('product::expense-head.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'title' => 'required|string|max:255|unique:expense_heads,title',
            'max_amount' => 'required|numeric|min:0',
            'status' => 'required|boolean'
        ], [
            'title.required' => 'Expense head title is required.',
            'title.unique' => 'This expense head title already exists.',
            'max_amount.required' => 'Maximum amount is required.',
            'max_amount.numeric' => 'Maximum amount must be a number.',
            'max_amount.min' => 'Maximum amount must be greater than or equal to 0.',
            'status.required' => 'Status is required.'
        ]);

        try {
            ExpenseHead::create([
                'title' => $request->title,
                'max_amount' => $request->max_amount,
                'status' => $request->status
            ]);

            return redirect()->route('admin.expenseHeadIndex')->with('success', 'Expense Head created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create expense head. Please try again.')->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExpenseHead $expenseHead)
    {
        return view('product::expense-head.edit', compact('expenseHead'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseHead $expenseHead)
    {
        // Validation
        $request->validate([
            'title' => 'required|string|max:255|unique:expense_heads,title,' . $expenseHead->id,
            'max_amount' => 'required|numeric|min:0',
            'status' => 'required|boolean'
        ], [
            'title.required' => 'Expense head title is required.',
            'title.unique' => 'This expense head title already exists.',
            'max_amount.required' => 'Maximum amount is required.',
            'max_amount.numeric' => 'Maximum amount must be a number.',
            'max_amount.min' => 'Maximum amount must be greater than or equal to 0.',
            'status.required' => 'Status is required.'
        ]);

        try {
            $expenseHead->update([
                'title' => $request->title,
                'max_amount' => $request->max_amount,
                'status' => $request->status
            ]);

            return redirect()->route('admin.expenseHeadIndex')->with('success', 'Expense Head updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update expense head. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseHead $expenseHead)
    {
        try {
            // Check if there are any expense lists associated
            if ($expenseHead->expenseLists()->count() > 0) {
                return back()->with('error', 'Cannot delete expense head. There are expenses associated with it.');
            }

            $expenseHead->delete();
            return redirect()->route('admin.expenseHeadIndex')->with('success', 'Expense Head deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete expense head. Please try again.');
        }
    }
}
