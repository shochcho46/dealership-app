<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Bank;
use Modules\Product\Models\BankAccountDetail;

class BankAccountDetailController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 30);
        $query = BankAccountDetail::with(['bank', 'creator']);

        // Filter by bank
        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->bank_id);
        }

        // Filter by transaction type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        $totalCredit = (clone $query)->where('type', 1)->sum('amount');
        $totalDebit  = (clone $query)->where('type', 2)->sum('amount');

        $details = $query->latest('transaction_date')->paginate($limit);

        // Calculate totals for filtered results

        $balance = $totalCredit - $totalDebit;


        $banks = Bank::active()->orderBy('bank_name')->get();

        return view('product::bank-account-detail.index', compact('details', 'banks', 'totalCredit', 'totalDebit', 'balance'));
    }

    public function create()
    {
        $banks = Bank::active()->orderBy('bank_name')->get();
        return view('product::bank-account-detail.create', compact('banks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:1,2',
            'transaction_date' => 'required|date',
            'note' => 'nullable|string'
        ]);

        try {
            BankAccountDetail::create($request->all());
            return redirect()->route('admin.bankAccountDetailIndex')->with('success', 'Transaction created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create transaction.')->withInput();
        }
    }

    public function edit(BankAccountDetail $bankAccountDetail)
    {
        $banks = Bank::active()->orderBy('bank_name')->get();
        return view('product::bank-account-detail.edit', compact('bankAccountDetail', 'banks'));
    }

    public function update(Request $request, BankAccountDetail $bankAccountDetail)
    {
        $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:1,2',
            'transaction_date' => 'required|date',
            'note' => 'nullable|string'
        ]);

        try {
            $bankAccountDetail->update($request->except('created_by'));
            return redirect()->route('admin.bankAccountDetailIndex')->with('success', 'Transaction updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update transaction.')->withInput();
        }
    }

    public function destroy(BankAccountDetail $bankAccountDetail)
    {
        try {
            $bankAccountDetail->delete();
            return redirect()->route('admin.bankAccountDetailIndex')->with('success', 'Transaction deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete transaction.');
        }
    }
}
