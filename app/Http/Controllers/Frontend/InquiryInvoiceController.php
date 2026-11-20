<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Routing\Controller;
use Modules\Admin\Entities\Business;
use Modules\Product\Models\WebsiteInquiry;

/**
 * Public customer invoice opened from the confirmation SMS link: /i/{code}.
 * Only confirmed (Contacted / Closed) orders have an invoice.
 */
class InquiryInvoiceController extends Controller
{
    public function show(string $code)
    {
        $inquiry = WebsiteInquiry::where('invoice_code', $code)
            ->whereIn('status', [WebsiteInquiry::STATUS_CONTACTED, WebsiteInquiry::STATUS_CLOSED])
            ->firstOrFail()
            ->ensurePriced();

        return response()
            ->view('frontend.invoice', [
                'inquiry'  => $inquiry,
                'products' => $inquiry->productModels(),
                'business' => Business::first(),
                'isAdmin'  => false,
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
