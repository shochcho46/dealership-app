<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\ProfitDisbursement;
use Modules\Product\Models\Investor;

class ProfitDisbursementController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 30);
        $query = ProfitDisbursement::with('investor');

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('disbursement_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('disbursement_date', '<=', $request->end_date);
        }

        // Filter by investor
        if ($request->filled('investor_id')) {
            $query->where('investor_id', $request->investor_id);
        }

        $disbursements = $query->latest('disbursement_date')->paginate($limit);
        $investors = Investor::active()->orderBy('name')->get();
        return view('product::profit-disbursement.index', compact('disbursements', 'investors'));
    }

    public function create()
    {
        $investors = Investor::active()->orderBy('name')->get();
        return view('product::profit-disbursement.create', compact('investors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'investor_id' => 'required|exists:investors,id',
            'amount' => 'required|numeric|min:0',
            'disbursement_date' => 'required|date',
            'note' => 'nullable|string'
        ]);

        try {
            ProfitDisbursement::create($request->all());
            return redirect()->route('admin.profitDisbursementIndex')->with('success', 'Profit Disbursement created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create disbursement.')->withInput();
        }
    }

    public function edit(ProfitDisbursement $profitDisbursement)
    {
        $investors = Investor::active()->orderBy('name')->get();
        return view('product::profit-disbursement.edit', compact('profitDisbursement', 'investors'));
    }

    public function update(Request $request, ProfitDisbursement $profitDisbursement)
    {
        $request->validate([
            'investor_id' => 'required|exists:investors,id',
            'amount' => 'required|numeric|min:0',
            'disbursement_date' => 'required|date',
            'note' => 'nullable|string'
        ]);

        try {
            $profitDisbursement->update($request->all());
            return redirect()->route('admin.profitDisbursementIndex')->with('success', 'Profit Disbursement updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update disbursement.')->withInput();
        }
    }

    public function destroy(ProfitDisbursement $profitDisbursement)
    {
        try {
            $profitDisbursement->delete();
            return redirect()->route('admin.profitDisbursementIndex')->with('success', 'Profit Disbursement deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete disbursement.');
        }
    }
}
