<?php

namespace Modules\Product\Http\Controllers;

use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Entities\Business;
use Modules\Product\Models\WebsiteInquiry;

/**
 * Website order settings: delivery fee and the inquiry confirmation SMS.
 * Stored on the single `businesses` row.
 */
class WebsiteSettingController extends Controller
{
    public function index()
    {
        abort_unless(auth('admin')->user()?->can('website_settings'), 403);

        $business = $this->business();

        // Example SMS built from a sample order, so the admin sees length before switching it on.
        $sample = new WebsiteInquiry([
            'name' => 'Rahim Uddin', 'subtotal' => 1250, 'delivery_fee' => $business->currentDeliveryFee(),
            'invoice_code' => 'Ab3xK9pQ',
        ]);
        $sample->id = 125;
        $sample->total = 1250 + $business->currentDeliveryFee();
        $smsPreview = $sample->confirmationSms($business);

        return view('product::website-settings.index', compact('business', 'smsPreview'));
    }

    public function update(Request $request)
    {
        abort_unless(auth('admin')->user()?->can('website_settings'), 403);

        $data = $request->validate([
            'delivery_fee_enabled' => 'nullable|boolean',
            'delivery_fee'         => 'nullable|numeric|min:0|max:100000',
            'inquiry_sms_enabled'  => 'nullable|boolean',
        ]);

        $this->business()->update([
            'delivery_fee_enabled' => $request->boolean('delivery_fee_enabled'),
            'delivery_fee'         => $data['delivery_fee'] ?? 0,
            'inquiry_sms_enabled'  => $request->boolean('inquiry_sms_enabled'),
        ]);

        return redirect()->route('website-settings.index')->with('success', 'Website settings saved.');
    }

    public function testSms(Request $request, SmsService $sms)
    {
        abort_unless(auth('admin')->user()?->can('website_settings'), 403);

        $request->validate(['test_mobile' => 'required|string|max:20']);

        $result = $sms->send($request->test_mobile, 'Test SMS from ' . $this->business()->shortName() . '. SMS is working.');

        return redirect()->route('website-settings.index')
            ->with($result['ok'] ? 'success' : 'error', ($result['ok'] ? 'Test SMS sent. ' : 'Test SMS failed: ') . $result['response']);
    }

    private function business(): Business
    {
        return Business::first() ?? Business::create(['company_name' => config('app.name')]);
    }
}
