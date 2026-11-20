<?php

namespace App\Http\Controllers\Frontend;

use App\Services\Frontend\CatalogService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Entities\Business;
use Modules\Product\Models\WebsiteInquiry;

class InquiryController extends Controller
{
    public function __construct(private CatalogService $catalog)
    {
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'mobile'          => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'area'            => 'nullable|string|max:150',
            'delivery_type'   => 'nullable|in:shop,home',
            'message'         => 'nullable|string|max:2000',
            'product_id'      => 'nullable|integer',
            'items'           => 'nullable|array|max:100',
            'items.*.id'      => 'required_with:items|integer',
            'items.*.cases'   => 'required_with:items|integer|min:1|max:100000',
        ]);

        // Only accept products that are actually on the public catalog,
        // and take names from the DB, not from the browser.
        $items = collect($data['items'] ?? [])
            ->map(function ($row) {
                $product = $this->catalog->find((int) $row['id']);
                // Price is taken from the catalog (never from the browser) and saved with the order.
                return $product ? WebsiteInquiry::lineFromCatalog($product, (int) $row['cases']) : null;
            })
            ->filter()
            ->values()
            ->all();

        $productId = isset($data['product_id']) && $this->catalog->find((int) $data['product_id'])
            ? (int) $data['product_id'] : null;

        // A single-product inquiry (product page form) becomes one line with qty 1.
        if (!$items && $productId) {
            $items = [WebsiteInquiry::lineFromCatalog($this->catalog->find($productId), 1)];
        }

        $business = Business::first();

        $inquiry = new WebsiteInquiry([
            'name'       => $data['name'],
            'mobile'     => $data['mobile'],
            'area'       => $data['area'] ?? null,
            'delivery_type' => $data['delivery_type'] ?? null,
            'message'    => $data['message'] ?? null,
            'product_id' => $productId,
            'items'      => $items ?: null,
            'delivery_fee' => $business ? $business->currentDeliveryFee() : 0,
            'status'     => WebsiteInquiry::STATUS_NEW,
            'ip'         => $request->ip(),
        ]);
        $inquiry->recalculate()->save();

        return $this->done($request);
    }

    private function done(Request $request)
    {
        $message = __('Thank you! We received your request and will call you shortly.');

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return back()->with('inquiry_success', $message);
    }
}
