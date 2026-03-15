<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FinancialReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = FinancialReport::with('creator')->orderBy('start_date', 'desc');

        // Date range filter
        if ($request->filled('filter_start_date') || $request->filled('filter_end_date')) {
            $query->dateRange($request->filter_start_date, $request->filter_end_date);
        }

        $reports = $query->paginate(15)->withQueryString();

        // Calculate totals for filtered results
        $totalsQuery = FinancialReport::query();

        if ($request->filled('filter_start_date') || $request->filled('filter_end_date')) {
            $totalsQuery->dateRange($request->filter_start_date, $request->filter_end_date);
        }

        $totals = $totalsQuery->selectRaw('
            SUM(total_sales) as sum_sales,
            SUM(actual_collected_amount) as sum_collected,
            SUM(total_purchase) as sum_purchase,
            SUM(total_expense) as sum_expense,
            SUM(discount_amount) as sum_discount,
            SUM(total_lost_amount) as sum_lost,
            SUM(total_damage_amount) as sum_damage,
            SUM(total_profit) as sum_profit,
            SUM(total_sales - actual_collected_amount) as sum_outstanding,
            SUM(actual_collected_amount - total_purchase - total_expense - discount_amount - total_lost_amount - total_damage_amount) as sum_actual_profit,
            SUM(total_sales - total_purchase - total_expense - discount_amount - total_lost_amount - total_damage_amount) as sum_expected_profit,
            SUM(profit_for_shareholders) as sum_shareholders,
            SUM(profit_for_sadaqah) as sum_sadaqah,
            SUM(profit_to_retain) as sum_retain
        ')->first();

        return view('product::financial-report.index', compact('reports', 'totals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('product::financial-report.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_sales' => 'required|numeric|min:0',
            'actual_collected_amount' => 'nullable|numeric|min:0',
            'total_purchase' => 'required|numeric|min:0',
            'total_expense' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'total_lost_amount' => 'nullable|numeric|min:0',
            'total_damage_amount' => 'nullable|numeric|min:0',
            'total_profit' => 'required|numeric',
            'profit_for_shareholders' => 'nullable|numeric|min:0',
            'profit_for_sadaqah' => 'nullable|numeric|min:0',
            'profit_to_retain' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        FinancialReport::create($validated);

        return redirect()->route('admin.financialReportIndex')
            ->with('success', 'Financial report created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FinancialReport $financialReport)
    {
        $financialReport->load('creator');
        return view('product::financial-report.show', compact('financialReport'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FinancialReport $financialReport)
    {
        return view('product::financial-report.edit', compact('financialReport'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FinancialReport $financialReport)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_sales' => 'required|numeric|min:0',
            'actual_collected_amount' => 'nullable|numeric|min:0',
            'total_purchase' => 'required|numeric|min:0',
            'total_expense' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'total_lost_amount' => 'nullable|numeric|min:0',
            'total_damage_amount' => 'nullable|numeric|min:0',
            'total_profit' => 'required|numeric',
            'profit_for_shareholders' => 'nullable|numeric|min:0',
            'profit_for_sadaqah' => 'nullable|numeric|min:0',
            'profit_to_retain' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $financialReport->update($validated);

        return redirect()->route('admin.financialReportIndex')
            ->with('success', 'Financial report updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FinancialReport $financialReport)
    {
        $financialReport->delete();

        return redirect()->route('admin.financialReportIndex')
            ->with('success', 'Financial report deleted successfully.');
    }
}
