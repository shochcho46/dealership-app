<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Bank;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 30);
        $query = Bank::withCount('bankAccountDetails');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $banks = $query->latest()->paginate($limit);
        return view('product::bank.index', compact('banks'));
    }

    public function create()
    {
        return view('product::bank.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_name' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'status' => 'required|boolean'
        ]);

        try {
            Bank::create($request->all());
            return redirect()->route('admin.bankIndex')->with('success', 'Bank created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create bank.')->withInput();
        }
    }

    public function edit(Bank $bank)
    {
        return view('product::bank.edit', compact('bank'));
    }

    public function update(Request $request, Bank $bank)
    {
        $request->validate([
            'bank_name' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'status' => 'required|boolean'
        ]);

        try {
            $bank->update($request->all());
            return redirect()->route('admin.bankIndex')->with('success', 'Bank updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update bank.')->withInput();
        }
    }

    public function destroy(Bank $bank)
    {
        try {
            $bank->delete();
            return redirect()->route('admin.bankIndex')->with('success', 'Bank deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete bank.');
        }
    }

    /**
     * Show bank account details/transactions
     */
    public function transactions(Bank $bank)
    {
        return redirect()->route('admin.bankAccountDetailIndex', ['bank_id' => $bank->id]);
    }
}
