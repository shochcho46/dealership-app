<?php

namespace Modules\Product\Http\Controllers;

use App\Services\Frontend\CatalogService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Product\Models\Product;

/**
 * Chooses which product photos float in the public website hero (home page banner).
 * Each of the 5 slots can be pinned to a product (products.hero_position);
 * empty slots are filled automatically by CatalogService::heroSlots().
 */
class WebsiteHeroController extends Controller
{
    public function index(CatalogService $catalog)
    {
        abort_unless(auth('admin')->user()?->can('website_settings'), 403);

        // Products that can be pinned: active and with at least one image.
        $candidates = Product::active()
            ->with(['media', 'company:id,name', 'stocks'])
            ->orderBy('name')
            ->get()
            ->filter(fn (Product $p) => $p->websiteMedia() !== null)
            ->values();

        $onWebsite = $catalog->products()->keys()->flip();   // in-stock products shown on the site
        $pinned    = Product::with('media')->whereNotNull('hero_position')->get()->keyBy('hero_position');
        $shown     = $catalog->heroSlots()->keyBy('slot');    // what the website shows right now

        return view('product::website-hero.index', compact('candidates', 'onWebsite', 'pinned', 'shown'));
    }

    public function update(Request $request)
    {
        abort_unless(auth('admin')->user()?->can('website_settings'), 403);

        $validated = $request->validate([
            'slots'   => 'nullable|array',
            'slots.*' => 'nullable|integer|exists:products,id',
        ]);

        $slots = collect($validated['slots'] ?? [])
            ->only(range(1, Product::HERO_SLOTS))
            ->filter();

        if ($slots->duplicates()->isNotEmpty()) {
            return back()->withInput()->with('error', 'The same product is chosen for more than one slot.');
        }

        DB::transaction(function () use ($slots) {
            Product::whereNotNull('hero_position')->update(['hero_position' => null]);
            foreach ($slots as $slot => $productId) {
                Product::whereKey($productId)->update(['hero_position' => (int) $slot]);
            }
        });

        // Query-builder updates don't fire model events, so refresh the website cache here.
        CatalogService::flush();

        return redirect()->route('website-hero.index')->with('success', 'Hero images updated. The website shows them now.');
    }
}
