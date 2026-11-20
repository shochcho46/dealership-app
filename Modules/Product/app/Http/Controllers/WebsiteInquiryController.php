<?php

namespace Modules\Product\Http\Controllers;

use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Entities\Business;
use Modules\Product\Models\Product;
use Modules\Product\Models\WebsiteInquiry;

class WebsiteInquiryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeInquiry();

        $query = WebsiteInquiry::with(['product:id,name', 'handledBy:id,name'])->latest();

        if ($request->filled('status')) {
            $query->where('status', (int) $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('shop_name', 'like', "%{$search}%")
                    ->orWhere('area', 'like', "%{$search}%");
            });
        }

        // Sums for everything matching the filters (date range, status, search), not just this page.
        $sumRow = fn ($q) => $q->selectRaw('count(*) as orders, coalesce(sum(subtotal),0) as subtotal, coalesce(sum(delivery_fee),0) as delivery_fee, coalesce(sum(total),0) as total')
            ->reorder()->first();
        $summary = [
            'all'       => $sumRow(clone $query),
            'confirmed' => $sumRow((clone $query)->whereIn('status', [WebsiteInquiry::STATUS_CONTACTED, WebsiteInquiry::STATUS_CLOSED])),
        ];

        $inquiries = $query->paginate(25)->withQueryString();
        $inquiries->getCollection()->each->ensurePriced();
        $counts = WebsiteInquiry::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        // Product images for all listed items, in one query.
        $productIds = $inquiries->getCollection()
            ->flatMap(fn ($i) => collect($i->items ?: [])->pluck('product_id')->push($i->product_id))
            ->filter()->unique();
        $products = $productIds->isEmpty() ? collect()
            : Product::with('media')->whereIn('id', $productIds)->get()->keyBy('id');

        return view('product::website-inquiry.index', compact('inquiries', 'counts', 'products', 'summary'));
    }

    public function show(WebsiteInquiry $websiteInquiry)
    {
        $this->authorizeInquiry();

        $inquiry  = $websiteInquiry->ensurePriced()->load('handledBy:id,name');
        $products = $inquiry->productModels();
        $smsOn    = (bool) optional(Business::first())->inquiry_sms_enabled;

        return view('product::website-inquiry.show', compact('inquiry', 'products', 'smsOn'));
    }

    /**
     * Status change. First time an inquiry becomes "Contacted", the confirmation SMS is sent
     * (when Website Settings → Inquiry SMS is on).
     */
    public function update(Request $request, WebsiteInquiry $websiteInquiry, SmsService $sms)
    {
        $this->authorizeInquiry();

        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(WebsiteInquiry::STATUSES)),
        ]);

        if ((int) $request->status === WebsiteInquiry::STATUS_NEW && !$websiteInquiry->canReopen()) {
            return back()->with('error', "Confirmed orders can't be moved back to New.");
        }

        $websiteInquiry->ensurePriced();
        $wasContacted = $websiteInquiry->status === WebsiteInquiry::STATUS_CONTACTED;
        $websiteInquiry->update(['status' => (int) $request->status]);

        $message = 'Inquiry marked as ' . $websiteInquiry->status_label . '.';
        $error   = null;

        if (!$wasContacted && $websiteInquiry->status === WebsiteInquiry::STATUS_CONTACTED && !$websiteInquiry->sms_sent_at) {
            [$ok, $note] = $this->sendConfirmation($websiteInquiry, $sms);
            if ($ok === true) {
                $message .= ' Confirmation SMS sent to ' . $websiteInquiry->mobile . '.';
            } elseif ($ok === false) {
                $error = 'SMS could not be sent: ' . $note;
            }
        }

        return back()->with('success', $message)->with('error', $error);
    }

    /**
     * Save edited quantities / unit prices and the delivery fee, then recalculate totals.
     */
    public function updatePricing(Request $request, WebsiteInquiry $websiteInquiry)
    {
        $this->authorizeInquiry();

        if ($websiteInquiry->isPriceLocked()) {
            return back()->with('error', 'Order is confirmed — prices and delivery fee are locked.');
        }

        $data = $request->validate([
            'delivery_fee'       => 'required|numeric|min:0|max:1000000',
            'items'              => 'nullable|array',
            'items.*.cases'      => 'required|integer|min:1|max:100000',
            'items.*.unit_price' => 'required|numeric|min:0|max:10000000',
            'remove'             => 'nullable|array',
        ]);

        $items  = $websiteInquiry->ensurePriced()->items ?: [];
        $remove = array_map('intval', array_keys(array_filter($data['remove'] ?? [])));

        foreach ($data['items'] ?? [] as $i => $row) {
            if (isset($items[$i])) {
                $items[$i]['cases']      = (int) $row['cases'];
                $items[$i]['unit_price'] = round((float) $row['unit_price'], 2);
            }
        }
        $items = array_values(array_diff_key($items, array_flip($remove)));

        $websiteInquiry->items        = $items;
        $websiteInquiry->delivery_fee = $data['delivery_fee'];
        $websiteInquiry->recalculate()->save();

        return back()->with('success', 'Order prices updated. Total: ৳' . number_format((float) $websiteInquiry->total, 2));
    }

    /** Quick delivery-fee change from the inquiry list (only while the order is New). */
    public function updateDeliveryFee(Request $request, WebsiteInquiry $websiteInquiry)
    {
        $this->authorizeInquiry();

        if ($websiteInquiry->isPriceLocked()) {
            return back()->with('error', 'Order is confirmed — delivery fee is locked.');
        }

        $data = $request->validate([
            'delivery_fee' => 'required|numeric|min:0|max:1000000',
        ]);

        $websiteInquiry->ensurePriced();
        $websiteInquiry->delivery_fee = round((float) $data['delivery_fee'], 2);
        $websiteInquiry->recalculate()->save();

        return back()->with('success', "Delivery fee for {$websiteInquiry->name} set to ৳" . number_format((float) $websiteInquiry->delivery_fee, 2)
            . '. Total: ৳' . number_format((float) $websiteInquiry->total, 2));
    }

    /** Resend the confirmation SMS. */
    public function sendSms(WebsiteInquiry $websiteInquiry, SmsService $sms)
    {
        $this->authorizeInquiry();

        if (!$websiteInquiry->has_invoice) {
            return back()->with('error', 'Mark the inquiry as Contacted first.');
        }

        [$ok, $note] = $this->sendConfirmation($websiteInquiry->ensurePriced(), $sms, true);

        return $ok
            ? back()->with('success', 'Confirmation SMS sent to ' . $websiteInquiry->mobile . '.')
            : back()->with('error', 'SMS could not be sent: ' . $note);
    }

    /** Admin view of the customer invoice (same page the SMS link opens). */
    public function invoice(WebsiteInquiry $websiteInquiry)
    {
        $this->authorizeInquiry();

        $inquiry = $websiteInquiry->ensurePriced();

        return view('frontend.invoice', [
            'inquiry'  => $inquiry,
            'products' => $inquiry->productModels(),
            'business' => Business::first(),
            'isAdmin'  => true,
        ]);
    }

    public function destroy(WebsiteInquiry $websiteInquiry)
    {
        $this->authorizeInquiry();

        $websiteInquiry->delete();

        return back()->with('success', 'Inquiry deleted.');
    }

    /**
     * @return array{0: bool|null, 1: string}  null = SMS switched off (not attempted)
     */
    private function sendConfirmation(WebsiteInquiry $inquiry, SmsService $sms, bool $force = false): array
    {
        $business = Business::first();

        if (!$force && !optional($business)->inquiry_sms_enabled) {
            $inquiry->forceFill(['sms_status' => 'disabled'])->saveQuietly();
            return [null, 'SMS is switched off in Website Settings'];
        }

        $result = $sms->send($inquiry->mobile, $inquiry->confirmationSms($business));

        $inquiry->forceFill([
            'sms_status'   => $result['ok'] ? 'sent' : 'failed',
            'sms_sent_at'  => $result['ok'] ? now() : $inquiry->sms_sent_at,
            'sms_response' => mb_substr($result['response'], 0, 250),
        ])->saveQuietly();

        return [$result['ok'], $result['response']];
    }

    private function authorizeInquiry(): void
    {
        abort_unless(auth('admin')->user()?->can('website_inquiry'), 403);
    }
}
