<?php

namespace Modules\Product\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Models\Order;
use Modules\Product\Models\VendorAccount;
use Modules\Product\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Admin\Entities\Business;
use Modules\Product\Models\Company;
use setasign\Fpdi\Fpdi;

class InvoiceController extends Controller
{
    /**
     * Generate single invoice
     */
    public function generateInvoice(Order $order)
    {

        // Check if order is shipped or delivered
        if (!in_array($order->order_status_id, [4, 5])) {
            return back()->with('error', 'Invoice can only be generated for shipped or delivered orders.');
        }

        $order->load(['vendor', 'orderItems.product', 'orderStatus', 'admin']);

        // Get vendor account information for dues calculation
        $vendorTotalDue = VendorAccount::where('vendor_id', $order->vendor_id)
            ->where('type', 1) // Debit type

            ->sum('amount');

        $vendorPaidAmount = VendorAccount::where('vendor_id', $order->vendor_id)
            ->where('type', 2) // Credit type

            ->sum('amount');
        $vendorNetDue = $vendorTotalDue - $vendorPaidAmount;
        $previousdue =  $vendorTotalDue - $order->total_amount;

        $currentInvoicePayment = VendorAccount::where('vendor_id', $order->vendor_id)->where('order_id', $order->id)
                                ->where('type', 2) // Debit type
                                ->sum('amount');

        $pdf = Pdf::setOptions([
            'margin-top' => 5,
            'margin-right' => 5,
            'margin-bottom' => 5,
            'margin-left' => 5,
            'dpi' => 150,
            'defaultFont' => 'dejavu sans',
            'isHtml5ParserEnabled' => true, // Enable HTML5 parsing
            'isRemoteEnabled' => true, // Allow loading remote assets (e.g., fonts, logo)
            'isFontSubsettingEnabled' => true, // Reduce font file size
            'chroot' => public_path(), // Restrict file access to public directory
            'defaultMediaType' => 'print', // Optimize for print
        ]);

        $pdf->loadView('product::invoice.template', [
            'order' => $order,
            'vendor' => $order->vendor,
            'orderItems' => $order->orderItems,
            'vendorTotalDue' => $vendorNetDue,
            'vendorPaidAmount' => $vendorPaidAmount,
            'previousDue' => $previousdue,
            'companyInfo' => $this->getCompanyInfo(),
            'currentInvoicePayment' => $currentInvoicePayment,
            'preview' => true
        ])
            ->setPaper('A4', 'portrait');


        $pdf->getDomPDF()->set_option('isPhpEnabled', true);
        // return $pdf->download("Invoice-{$order->invoice_id}.pdf");

        return $pdf->stream("Invoice-{$order->invoice_id}.pdf");


    }

    /**
     * Preview invoice in browser
     */
    public function previewInvoice(Order $order)
    {
        // Check if order is shipped or delivered
        if (!in_array($order->order_status_id, [4, 5])) {
            return back()->with('error', 'Invoice can only be generated for shipped or delivered orders.');
        }

        $order->load(['vendor', 'orderItems.product', 'orderStatus', 'admin']);

        // Get vendor account information for dues calculation
        $vendorTotalDue = VendorAccount::where('vendor_id', $order->vendor_id)
            ->where('type', 1) // Debit type

            ->sum('amount');

        $vendorPaidAmount = VendorAccount::where('vendor_id', $order->vendor_id)
            ->where('type', 2) // Debit type

            ->sum('amount');
        $previousdue =  $vendorTotalDue - $order->total_amount;

        $vendorNetDue = $vendorTotalDue - $vendorPaidAmount;

        $currentInvoicePayment = VendorAccount::where('vendor_id', $order->vendor_id)->where('order_id', $order->id)
                                ->where('type', 2) // Debit type
                                ->sum('amount');
        return view('product::invoice.template', [
            'order' => $order,
            'vendor' => $order->vendor,
            'orderItems' => $order->orderItems,
            'vendorTotalDue' => $vendorNetDue,
            'vendorPaidAmount' => $vendorPaidAmount,
            'previousDue' => $previousdue,
            'currentInvoicePayment' => $currentInvoicePayment,
            'companyInfo' => $this->getCompanyInfo(),
            'preview' => true
        ]);
    }




    public function bulkInvoices(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id'
        ]);

        $orders = Order::with(['vendor', 'orderItems.product', 'orderStatus', 'admin'])
            ->whereIn('id', $request->order_ids)
            ->whereIn('order_status_id', [4, 5]) // shipped or delivered
            ->get();

        if ($orders->isEmpty()) {
            return back()->with('error', 'No valid orders found for invoice generation.');
        }

        // Ensure temp directory exists
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFiles = [];

        // Step 1: Generate individual PDFs
        foreach ($orders as $order) {
            $vendorTotalDue = VendorAccount::where('vendor_id', $order->vendor_id)
                ->where('type', 1)
                ->sum('amount');

            $vendorPaidAmount = VendorAccount::where('vendor_id', $order->vendor_id)
                ->where('type', 1)
                ->sum('amount');

            $previousDue = $vendorTotalDue - $order->total_amount;

            $currentInvoicePayment = VendorAccount::where('vendor_id', $order->vendor_id)->where('order_id', $order->id)
                                ->where('type', 2) // Debit type
                                ->sum('amount');

            $data = [
                'order' => $order,
                'vendor' => $order->vendor,
                'orderItems' => $order->orderItems,
                'vendorTotalDue' => $vendorTotalDue,
                'vendorPaidAmount' => $vendorPaidAmount,
                'previousDue' => $previousDue,
                'currentInvoicePayment' => $currentInvoicePayment,
                'companyInfo' => $this->getCompanyInfo(),
            ];

            // Generate individual invoice
            $pdf = Pdf::loadView('product::invoice.template', $data)
                ->setPaper('A4', 'portrait');

            // Save each temporary file
            $tempPath = "{$tempDir}/invoice_{$order->id}.pdf";
            $pdf->save($tempPath);
            $tempFiles[] = $tempPath;
        }

        // Step 2: Merge PDFs into one
        $mergedPath = "{$tempDir}/bulk-invoices-" . date('Y-m-d-H-i-s') . ".pdf";
        $fpdi = new Fpdi();

        foreach ($tempFiles as $file) {
            $pageCount = $fpdi->setSourceFile($file);

            for ($page = 1; $page <= $pageCount; $page++) {
                $tpl = $fpdi->importPage($page);
                $size = $fpdi->getTemplateSize($tpl);
                $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $fpdi->useTemplate($tpl);
            }
        }

        $fpdi->Output($mergedPath, 'F');

        // Step 3: Clean up individual temp files
        foreach ($tempFiles as $file) {
            @unlink($file);
        }

        // Step 4: Return merged file for download
        return response()->download($mergedPath, 'bulk-invoices.pdf')->deleteFileAfterSend(true);
    }


    /**
     * Invoice management page
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 50;


       $query = Order::with(['vendor', 'orderStatus', 'orderItems', 'vendorAccounts'])
                ->whereIn('order_status_id', [4, 5]); // Shipped or delivered only

        // Apply filters
        if ($request->filled('invoice_search')) {
            $query->where('invoice_id', 'like', '%' . $request->invoice_search . '%');
        }

        if ($request->filled('vendor_filter')) {
            $query->where('vendor_id', $request->vendor_filter);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('place_by_filter')) {
            $query->where('place_by', $request->place_by_filter);
        }

        if ($request->filled('payment_status_filter')) {
            $query->where('payment_status', $request->payment_status_filter);
        }
        if ($request->filled('company_filter')) {
            $query->whereHas('orderItems.product', function ($q) use ($request) {
                $q->where('company_id', $request->company_filter);
            });
        }

        $fullQuery = clone $query;

        $orders = $query->orderBy('id', 'desc')->paginate($limit)->appends($request->query());



        // PAGE totals (collection sums)
        $pageTotalAmount = $orders->sum('total_amount');
        // $pageTotalPaidAmount = $orders->sum(function ($order) {
        //     return $order->vendorAccounts->where('type',2)->sum('amount');
        // });
        $pageTotalPaidAmount = $orders->sum('paid_amount');
        $pageTotalDueAmount = $pageTotalAmount - $pageTotalPaidAmount;


       // FILTERED totals (all matching rows)
        $filteredOrders = $fullQuery->get(); // get as collection
        $filteredTotalAmount = $filteredOrders->sum('total_amount');
        $filteredTotalPaidAmount = $filteredOrders->sum('paid_amount');
        $filteredTotalDueAmount = $filteredTotalAmount - $filteredTotalPaidAmount;

        // Summary data
        $totalInvoices = Order::whereIn('order_status_id', [4, 5])->count();
        $totalInvoiceAmount = Order::whereIn('order_status_id', [4, 5])->sum('total_amount');
        $paidInvoices = Order::whereIn('order_status_id', [4, 5])->where('payment_status', 2)->count();
        $unpaidInvoices = Order::whereIn('order_status_id', [4, 5])->where('payment_status', 0)->count();

        // Get vendors for filter
        $vendors = Vendor::orderBy('shop_name')->get();
        $companyName = Company::orderBy('name')->get();
        $placeBys  = Admin::role(['admin', 'subadmin', 'dsr', 'sr'])->orderBy('name')->get();
        return view('product::invoice.index', compact(
            'orders',
            'totalInvoices',
            'totalInvoiceAmount',
            'paidInvoices',
            'unpaidInvoices',
            'vendors',
            'companyName',
            'placeBys',
            'pageTotalAmount',
            'filteredTotalAmount',
            'pageTotalPaidAmount',
            'filteredTotalPaidAmount',
            'pageTotalDueAmount',
            'filteredTotalDueAmount'
        ));
    }

    /**
     * Get company information for invoice header
     */
    private function getCompanyInfo()
    {
        $business = Business::first();
        if ($business) {
            return [
                'name' => $business->company_name,
                'address' => $business->address,
                'phone' => $business->mobile_one,
                'phone_two' => $business->mobile_two,
                'email' => $business->email,
                'website' => 'https://www.bddealership.com',
            ];
        }
        return [
            'name' => config('app.name', 'Inventory Management System'),
            'address' => 'Your Company Address Here',
            'phone' => '+880 1234 567890',
            'email' => 'info@yourcompany.com',
            'website' => 'www.yourcompany.com',
            'logo' => public_path('assets/images/logo.png') // Adjust path as needed
        ];
    }
}
