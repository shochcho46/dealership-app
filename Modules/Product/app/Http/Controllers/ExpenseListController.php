<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\ExpenseHead;
use Modules\Product\Models\ExpenseList;

class ExpenseListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 30);
        $expenseHeads = ExpenseHead::active()->get();

        $query = ExpenseList::with('expenseHead')->orderBy('id', 'desc');

        if ($request->filled('expense_head_id')) {
            $query = $query->where('expense_head_id', $request->expense_head_id);
        }
        if ($request->filled('date_from')) {
            $query = $query->whereDate('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query = $query->whereDate('expense_date', '<=', $request->date_to);
        }

        // Calculate total for all filtered records
        $totalAll = (clone $query)->sum('amount');

        // Paginate and calculate total for current page
        $expenseLists = $query->paginate($limit)->appends($request->query());
        $totalPage = $expenseLists->sum('amount');

        $filters = [
            'expense_head_id' => $request->expense_head_id,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
        ];

        return view('product::expense-list.index', compact('expenseLists', 'expenseHeads', 'filters', 'totalPage', 'totalAll'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $expenseHeads = ExpenseHead::active()->get();
        return view('product::expense-list.create', compact('expenseHeads'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'expense_head_id' => 'required|exists:expense_heads,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'reference_no' => 'nullable|string|max:255',
            'status' => 'required|boolean'
        ], [
            'expense_head_id.required' => 'Expense head is required.',
            'expense_head_id.exists' => 'Selected expense head does not exist.',
            'title.required' => 'Expense title is required.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be greater than or equal to 0.',
            'expense_date.required' => 'Expense date is required.',
            'expense_date.date' => 'Expense date must be a valid date.',
            'status.required' => 'Status is required.'
        ]);

        try {
            // Check if amount exceeds max_amount for current month
            $expenseHead = ExpenseHead::find($request->expense_head_id);
            $expenseDateObj = \Carbon\Carbon::parse($request->expense_date);

            // Get total expenses for the month of the expense_date being added
            $totalExpensesForMonth = $expenseHead->expenseLists()
                ->whereYear('expense_date', $expenseDateObj->year)
                ->whereMonth('expense_date', $expenseDateObj->month)
                ->sum('amount');

            $newTotal = $totalExpensesForMonth + $request->amount;
            $remainingAmount = $expenseHead->max_amount - $totalExpensesForMonth;

            if ($newTotal > $expenseHead->max_amount) {
                return back()->with('error', 'Amount exceeds the maximum limit for this expense head for ' . $expenseDateObj->format('F Y') . '. Remaining: ৳' . number_format($remainingAmount, 2))->withInput();
            }

            ExpenseList::create([
                'expense_head_id' => $request->expense_head_id,
                'title' => $request->title,
                'description' => $request->description,
                'amount' => $request->amount,
                'expense_date' => $request->expense_date,
                'reference_no' => $request->reference_no,
                'status' => $request->status
            ]);

            return redirect()->route('admin.expenseListIndex')->with('success', 'Expense created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create expense. Please try again.')->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExpenseList $expenseList)
    {
        $expenseHeads = ExpenseHead::active()->get();
        return view('product::expense-list.edit', compact('expenseList', 'expenseHeads'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseList $expenseList)
    {
        // Validation
        $request->validate([
            'expense_head_id' => 'required|exists:expense_heads,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'reference_no' => 'nullable|string|max:255',
            'status' => 'required|boolean'
        ], [
            'expense_head_id.required' => 'Expense head is required.',
            'expense_head_id.exists' => 'Selected expense head does not exist.',
            'title.required' => 'Expense title is required.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be greater than or equal to 0.',
            'expense_date.required' => 'Expense date is required.',
            'expense_date.date' => 'Expense date must be a valid date.',
            'status.required' => 'Status is required.'
        ]);

        try {
            // Check if amount exceeds max_amount for current month
            $expenseHead = ExpenseHead::find($request->expense_head_id);
            $expenseDateObj = \Carbon\Carbon::parse($request->expense_date);

            // Get total expenses for the month of the expense_date being updated, excluding current record
            $totalExpensesForMonth = $expenseHead->expenseLists()
                ->where('id', '!=', $expenseList->id)
                ->whereYear('expense_date', $expenseDateObj->year)
                ->whereMonth('expense_date', $expenseDateObj->month)
                ->sum('amount');

            $newTotal = $totalExpensesForMonth + $request->amount;
            $remainingAmount = $expenseHead->max_amount - $totalExpensesForMonth;

            if ($newTotal > $expenseHead->max_amount) {
                return back()->with('error', 'Amount exceeds the maximum limit for this expense head for ' . $expenseDateObj->format('F Y') . '. Remaining: ৳' . number_format($remainingAmount, 2))->withInput();
            }

            $expenseList->update([
                'expense_head_id' => $request->expense_head_id,
                'title' => $request->title,
                'description' => $request->description,
                'amount' => $request->amount,
                'expense_date' => $request->expense_date,
                'reference_no' => $request->reference_no,
                'status' => $request->status
            ]);

            return redirect()->route('admin.expenseListIndex')->with('success', 'Expense updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update expense. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseList $expenseList)
    {
        try {
            $expenseList->delete();
            return redirect()->route('admin.expenseListIndex')->with('success', 'Expense deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete expense. Please try again.');
        }
    }
}
