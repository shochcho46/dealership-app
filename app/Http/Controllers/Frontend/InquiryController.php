<?php

namespace App\Http\Controllers\Frontend;

use App\Services\Frontend\CatalogService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Models\WebsiteInquiry;

class InquiryController extends Controller
{
    public function __construct(private CatalogService $catalog)
    {
    }

    public function store(Request $request)
    {
        // Honeypot: real visitors never fill this hidden field.
        if (filled($request->input('website'))) {
            return $this->done($request);
        }

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
                return $product ? [
                    'product_id' => $product['id'],
                    'name'       => $product['name'],
                    'cases'      => (int) $row['cases'],
                    'pack_unit'  => $product['per_pack'] ? $product['pack_unit'] : 'piece',
                ] : null;
            })
            ->filter()
            ->values()
            ->all();

        $productId = isset($data['product_id']) && $this->catalog->find((int) $data['product_id'])
            ? (int) $data['product_id'] : null;

        WebsiteInquiry::create([
            'name'       => $data['name'],
            'mobile'     => $data['mobile'],
            'area'       => $data['area'] ?? null,
            'delivery_type' => $data['delivery_type'] ?? null,
            'message'    => $data['message'] ?? null,
            'product_id' => $productId,
            'items'      => $items ?: null,
            'status'     => WebsiteInquiry::STATUS_NEW,
            'ip'         => $request->ip(),
        ]);

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
