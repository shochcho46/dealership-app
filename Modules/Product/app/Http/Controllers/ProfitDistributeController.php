<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\ProfitDistribute;
use Modules\Product\Models\ProfitDistributeDetail;

class ProfitDistributeController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 30);
        $query = ProfitDistribute::withCount('profitDistributeDetails');

           // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }


        $profitDistributes = $query->latest('year')->latest('month')->paginate($limit);
        return view('product::profit-distribute.index', compact('profitDistributes'));
    }

    public function create()
    {
        return view('product::profit-distribute.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'total_amount' => 'nullable|numeric|min:0',
            'status' => 'required|boolean'
        ]);

        try {
            ProfitDistribute::create($request->all());
            return redirect()->route('admin.profitDistributeIndex')->with('success', 'Profit Distribution created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create profit distribution.')->withInput();
        }
    }

    public function edit(ProfitDistribute $profitDistribute)
    {
        return view('product::profit-distribute.edit', compact('profitDistribute'));
    }

    public function update(Request $request, ProfitDistribute $profitDistribute)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'total_amount' => 'nullable|numeric|min:0',
            'status' => 'required|boolean'
        ]);

        try {
            $profitDistribute->update($request->all());
            return redirect()->route('admin.profitDistributeIndex')->with('success', 'Profit Distribution updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update profit distribution.')->withInput();
        }
    }

    public function destroy(ProfitDistribute $profitDistribute)
    {
        try {
            if ($profitDistribute->profitDistributeDetails()->count() > 0) {
                return back()->with('error', 'Cannot delete. There are details associated with it.');
            }
            $profitDistribute->delete();
            return redirect()->route('admin.profitDistributeIndex')->with('success', 'Profit Distribution deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete profit distribution.');
        }
    }

    public function showDetails(ProfitDistribute $profitDistribute)
    {
        $details = $profitDistribute->profitDistributeDetails()->latest()->paginate(20);
        return view('product::profit-distribute.details', compact('profitDistribute', 'details'));
    }

    public function storeDetail(Request $request, ProfitDistribute $profitDistribute)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:1,2',
            'date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        try {
            ProfitDistributeDetail::create([
                'profit_distribute_id' => $profitDistribute->id,
                'amount' => $request->amount,
                'type' => $request->type,
                'date' => $request->date,
                'description' => $request->description
            ]);

            return back()->with('success', 'Detail added successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add detail.')->withInput();
        }
    }

    public function destroyDetail(ProfitDistributeDetail $detail)
    {
        try {
            $detail->delete();
            return back()->with('success', 'Detail deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete detail.');
        }
    }
}
