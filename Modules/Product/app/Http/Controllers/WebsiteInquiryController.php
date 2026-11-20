<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Models\WebsiteInquiry;

class WebsiteInquiryController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth('admin')->user()?->can('website_inquiry'), 403);

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

        $inquiries = $query->paginate(25)->withQueryString();
        $counts = WebsiteInquiry::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('product::website-inquiry.index', compact('inquiries', 'counts'));
    }

    public function update(Request $request, WebsiteInquiry $websiteInquiry)
    {
        abort_unless(auth('admin')->user()?->can('website_inquiry'), 403);

        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(WebsiteInquiry::STATUSES)),
        ]);

        $websiteInquiry->update(['status' => (int) $request->status]);

        return back()->with('success', 'Inquiry marked as ' . $websiteInquiry->status_label . '.');
    }

    public function destroy(WebsiteInquiry $websiteInquiry)
    {
        abort_unless(auth('admin')->user()?->can('website_inquiry'), 403);

        $websiteInquiry->delete();

        return back()->with('success', 'Inquiry deleted.');
    }
}
