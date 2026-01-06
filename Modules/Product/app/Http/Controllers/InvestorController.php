<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Investor;
use Modules\Product\Models\InvestmentDetail;

class InvestorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $limit = request()->get('limit', 50);
        $investors = Investor::withCount('investmentDetails')->latest()->paginate($limit);
        return view('product::investor.index', compact('investors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('product::investor.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|boolean'
        ]);

        try {
            Investor::create($request->all());
            return redirect()->route('admin.investorIndex')->with('success', 'Investor created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create investor. Please try again.')->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Investor $investor)
    {
        return view('product::investor.edit', compact('investor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Investor $investor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|boolean'
        ]);

        try {
            $investor->update($request->all());
            return redirect()->route('admin.investorIndex')->with('success', 'Investor updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update investor. Please try again.')->withInput();
        }
    }


    /**
     * Update investor status
     */    public function updateStatus(Request $request, Investor $investor)
    {
        $request->validate([
            'status' => 'required|boolean'
        ]);

        try {
            $investor->update(['status' => $request->status]);
            return redirect()->route('admin.investorIndex')->with('success', 'Investor status updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update investor status. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Investor $investor)
    {
        try {
            if ($investor->investmentDetails()->count() > 0) {
                return back()->with('error', 'Cannot delete investor. There are investments associated with it.');
            }
            $investor->delete();
            return redirect()->route('admin.investorIndex')->with('success', 'Investor deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete investor. Please try again.');
        }
    }

    /**
     * Show investments for an investor
     */
    public function showInvestments(Investor $investor)
    {
        $investments = $investor->investmentDetails()->latest()->paginate(20);
        return view('product::investor.investments', compact('investor', 'investments'));
    }

    /**
     * Store investment detail
     */
    public function storeInvestment(Request $request, Investor $investor)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'investment_date' => 'required|date',
            'invoice' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:5120'
        ]);

        try {
            $investment = InvestmentDetail::create([
                'investor_id' => $investor->id,
                'amount' => $request->amount,
                'investment_date' => $request->investment_date
            ]);

            if ($request->hasFile('invoice')) {
                $investment->addMedia($request->file('invoice'))
                    ->toMediaCollection('investment_invoice');
            }

            return back()->with('success', 'Investment added successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add investment. Please try again.')->withInput();
        }
    }

    /**
     * Delete investment detail
     */
    public function destroyInvestment(InvestmentDetail $investment)
    {

        // Delete associated media
        $investment->clearMediaCollection('investment_invoice');
        $investment->delete();
        return redirect()->route('admin.investorInvestments',$investment->investor_id)->with('success', 'Investment deleted successfully!');

    }
}
