<?php

namespace Modules\Product\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Product\Exports\CommissionExport;
use Modules\Product\Models\OrderStatus;
use Modules\Product\Models\Vendor;
use Modules\Product\Services\CommissionReport;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeCommission();
        $this->validateFilters($request);

        $orders = CommissionReport::query($request)
            ->orderByDesc('orders.created_at')
            ->orderByDesc('orders.id')
            ->paginate(50)
            ->withQueryString();

        $totals    = CommissionReport::totals($request);
        $vendors   = Vendor::orderBy('shop_name')->get(['id', 'shop_name', 'mobile']);
        $placeBys  = Admin::role(['admin', 'subadmin', 'dsr', 'sr'])->orderBy('name')->get(['id', 'name']);
        $statuses  = OrderStatus::orderBy('id')->get(['id', 'name']);

        return view('product::commission.index', compact('orders', 'totals', 'vendors', 'placeBys', 'statuses'));
    }

    public function export(Request $request)
    {
        $this->authorizeCommission();
        $this->validateFilters($request);

        try {
            return Excel::download(new CommissionExport($request), 'commission_' . date('Y-m-d_His') . '.xlsx');
        } catch (\Throwable $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    private function validateFilters(Request $request): void
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'place_by'  => 'nullable|integer',
            'vendor_id' => 'nullable|integer',
            'status'    => 'nullable|string|max:10',
            'result'    => 'nullable|in:plus,minus',
        ]);
    }

    private function authorizeCommission(): void
    {
        abort_unless(auth('admin')->user()?->can('commission_list'), 403);
    }
}
