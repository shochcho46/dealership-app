<?php

namespace App\Services\Frontend;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Product\Models\Product;
use Modules\Product\Models\Vendor;

/**
 * Public catalog data for the website.
 *
 * Only products with available stock are exposed, and every product is mapped
 * to a plain array so cost fields (purchase_price, stock quantities, …) can
 * never reach the frontend.
 */
class CatalogService
{
    private const CACHE_KEY = 'frontend.catalog.v5';
    private const CACHE_TTL = 600; // seconds

    /** All in-stock products as plain arrays, keyed by product id. */
    public function products(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Product::active()
                ->with(['company:id,name', 'company.media', 'unit:id,name', 'media', 'stocks'])
                ->orderBy('name')
                ->get()
                ->map(fn (Product $p) => $this->toArray($p))
                ->filter()               // drops out-of-stock products (null)
                ->keyBy('id');
        });
    }

    public function find(int $id): ?array
    {
        return $this->products()->get($id);
    }

    /** Companies that have at least one in-stock product, with counts. */
    public function companies(): Collection
    {
        return $this->products()
            ->groupBy('company_id')
            ->map(fn ($items) => [
                'id'    => $items->first()['company_id'],
                'name'  => $items->first()['company'],
                'slug'  => Str::slug($items->first()['company']),
                'count' => $items->count(),
                'logo'  => $items->first()['company_logo'],
                'image' => optional($items->firstWhere('has_image', true))['image'],
            ])
            ->sortByDesc('count')
            ->values();
    }

    /** Products for the home page: in stock, with a photo, priced. */
    public function featured(int $limit = 8): Collection
    {
        return $this->products()
            ->filter(fn ($p) => $p['has_image'] && $p['price_piece'])
            ->sortByDesc('updated_at')
            ->take($limit)
            ->values();
    }

    /**
     * Product photos for the floating hero layers, in slot order (slot 1 = large centre card).
     *
     * Slots pinned in admin (products.hero_position) are used when that product is
     * on the website; empty slots are auto-filled with one photo per company.
     */
    public function heroImages(int $slots = Product::HERO_SLOTS): Collection
    {
        return $this->heroSlots($slots)->map(fn ($s) => $s['product']['image'])->values();
    }

    /** Hero slots with the product shown and whether it was pinned or auto-filled. */
    public function heroSlots(int $slots = Product::HERO_SLOTS): Collection
    {
        $withImage = $this->products()->filter(fn ($p) => $p['has_image']);

        $pinned = $withImage
            ->filter(fn ($p) => $p['hero_position'] >= 1 && $p['hero_position'] <= $slots)
            ->keyBy('hero_position');

        $used = $pinned->pluck('id')->all();
        $auto = $withImage
            ->reject(fn ($p) => in_array($p['id'], $used, true))
            ->groupBy('company_id')
            ->map->first()
            ->values()
            ->merge($withImage->reject(fn ($p) => in_array($p['id'], $used, true)))
            ->unique('id')
            ->values();

        $result = collect();
        for ($slot = 1; $slot <= $slots; $slot++) {
            if ($pinned->has($slot)) {
                $result->push(['slot' => $slot, 'pinned' => true, 'product' => $pinned->get($slot)]);
            } elseif ($auto->isNotEmpty()) {
                $result->push(['slot' => $slot, 'pinned' => false, 'product' => $auto->shift()]);
            }
        }

        return $result;
    }

    public function related(array $product, int $limit = 4): Collection
    {
        return $this->products()
            ->filter(fn ($p) => $p['company_id'] === $product['company_id'] && $p['id'] !== $product['id'])
            ->take($limit)
            ->values();
    }

    public function stats(): array
    {
        return Cache::remember(self::CACHE_KEY . '.stats', self::CACHE_TTL, fn () => [
            'products'  => $this->products()->count(),
            'companies' => $this->companies()->count(),
            'shops'     => Vendor::count(),
        ]);
    }

    /** Filter + sort for the catalog page. */
    public function search(?string $q, ?int $companyId, ?string $sort): Collection
    {
        $items = $this->products();

        if ($companyId) {
            $items = $items->where('company_id', $companyId);
        }

        if ($q !== null && trim($q) !== '') {
            $needle = Str::lower(trim($q));
            $items = $items->filter(fn ($p) => Str::contains(Str::lower($p['name'] . ' ' . $p['company']), $needle));
        }

        $sorted = match ($sort) {
            'price_asc'  => $items->sortBy(fn ($p) => $p['price_piece'] ?? PHP_INT_MAX),
            'price_desc' => $items->sortByDesc(fn ($p) => $p['price_piece'] ?? -1),
            'newest'     => $items->sortByDesc('created_at'),
            default      => $items->sortBy(fn ($p) => Str::lower($p['name'])),
        };

        return $sorted->values();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_KEY . '.stats');
        Cache::forget('frontend.catalog.v1');
        Cache::forget('frontend.catalog.v2');
        Cache::forget('frontend.catalog.v3');
        Cache::forget('frontend.catalog.v4');
    }

    // ------------------------------------------------------------------

    private function toArray(Product $p): ?array
    {
        $available = $p->stocks->filter(fn ($s) => $this->remaining($s) > 0);

        // Hide products with no available stock.
        if ($available->isEmpty()) {
            return null;
        }

        // Display price: the product's Website price (set in admin) when given,
        // otherwise highest sell price among available stock, otherwise latest non-zero sell price.
        $price = (float) $p->web_price;
        if ($price <= 0) {
            $price = (float) $available->max('sell_price');
        }
        if ($price <= 0) {
            $price = (float) optional(
                $p->stocks->where('sell_price', '>', 0)->sortByDesc('id')->first()
            )->sell_price;
        }
        $price = $price > 0 ? round($price, 2) : null;

        // Pack / case price.
        $innerQty   = (float) $p->measurement_unit_number;
        $innerUnit  = trim((string) $p->measurement_unit_name);
        $packQty    = max((float) $p->package_unit_quantity, 1);
        $packUnit   = Str::title(Str::lower(trim((string) $p->package_unit_name))) ?: 'Case';
        $isPieceish = (bool) preg_match('/^(pc|pcs|piece|pieces|pis)$/i', $innerUnit);

        $perPack = $isPieceish && $innerQty > 1 ? $innerQty / $packQty : null;
        $casePrice = ($price && $perPack) ? round($price * $perPack, 2) : null;

        // MRP and discount (MRP - website price), only when MRP is higher.
        $mrp         = (float) $p->mrp;
        $mrp         = ($price && $mrp > $price) ? round($mrp, 2) : null;
        $discountPct = $mrp ? (int) round(($mrp - $price) / $mrp * 100) : null;
        $caseMrp     = ($mrp && $perPack) ? round($mrp * $perPack, 2) : null;

        $unitLabel = $isPieceish || $innerUnit === ''
            ? 'piece'
            : Str::lower(optional($p->unit)->name ? preg_replace('/\(.*\)/', '', $p->unit->name) : 'piece');

        $packLabel = $innerQty > 0 && $innerUnit !== ''
            ? rtrim(rtrim(number_format($innerQty, 2, '.', ''), '0'), '.') . ' ' . Str::lower($innerUnit)
              . ' / ' . rtrim(rtrim(number_format($packQty, 2, '.', ''), '0'), '.') . ' ' . Str::lower($packUnit)
            : null;

        // Website image: the one chosen in admin (website_media_id), else the main image,
        // else the first other image. The chosen image is always first in the gallery.
        $allMedia = $p->allImages();
        $chosen   = $p->websiteMedia();
        $image   = $chosen ? $chosen->getUrl() : null;
        $gallery = $chosen
            ? collect([$chosen])->merge($allMedia->reject(fn ($m) => $m->id === $chosen->id))->map->getUrl()->values()->all()
            : [];

        return [
            'id'          => $p->id,
            'name'        => $p->name,
            'slug'        => Str::slug($p->name) ?: 'product',
            'company_id'  => $p->company_id,
            'company'     => optional($p->company)->name ?? 'Other',
            'company_logo' => optional($p->company)->logo_url,
            'description' => $p->description,
            'has_image'   => (bool) $image,
            'image'       => $image ?: asset('frontend/v2/img/product-placeholder.svg'),
            'gallery'     => $gallery,
            'pack_label'  => $packLabel,
            'per_pack'    => $perPack ? (int) round($perPack) : null,
            'pack_unit'   => $packUnit,
            'unit_label'  => trim($unitLabel) ?: 'piece',
            'price_piece' => $price,
            'hero_position' => $p->hero_position ?: null,
            'case_price'  => $casePrice,
            'mrp_piece'    => $mrp,
            'case_mrp'     => $caseMrp,
            'discount_pct' => $discountPct ?: null,
            'created_at'  => optional($p->created_at)->timestamp,
            'updated_at'  => optional($p->updated_at)->timestamp,
        ];
    }

    private function remaining($stock): float
    {
        return (float) $stock->quantity - (float) $stock->sold_quantity - (float) $stock->damage_quantity
            - (float) $stock->stolen_quantity - (float) $stock->transfer_quantity - (float) $stock->froze_quantity;
    }
}
