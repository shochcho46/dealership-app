@php use App\Support\Frontend; @endphp
@php
    $url      = route('products.show', [$product['id'], $product['slug']]);
    $orderBy  = $product['per_pack'] ? $product['pack_unit'] : __('piece');
    $unitStep = $product['per_pack'] ? ($product['case_price'] ?? 0) : ($product['price_piece'] ?? 0);
@endphp
<article class="p-card" data-reveal>
    <a href="{{ $url }}" class="p-media {{ $product['has_image'] ? '' : 'is-placeholder' }}">
        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy" decoding="async" width="300" height="300">
        <span class="badge-stock"><i class="mdi mdi-check-circle"></i> {{ __('In stock') }}</span>
        @if(!empty($product['discount_pct']))
            <span class="badge-off">-{{ Frontend::num($product['discount_pct']) }}%</span>
        @endif
    </a>
    <div class="p-body">
        <a href="{{ route('products.index', ['company' => $product['company_id']]) }}" class="p-company">{{ $product['company'] }}</a>
        <h3 class="p-title"><a href="{{ $url }}">{{ $product['name'] }}</a></h3>
        @if($product['pack_label'])
            <p class="p-pack"><i class="mdi mdi-package-variant-closed"></i> {{ Frontend::num($product['pack_label']) }}</p>
        @endif

        <div class="p-price">
            @if($product['price_piece'])
                <div class="price-row">
                    <strong>{{ Frontend::money($product['price_piece']) }}</strong>
                    <span>/ {{ __($product['unit_label']) }}</span>
                    @if(!empty($product['mrp_piece']))
                        <del class="p-mrp" title="{{ __('MRP') }}">{{ Frontend::money($product['mrp_piece']) }}</del>
                    @endif
                </div>
                @if($product['case_price'])
                    <div class="price-row case">
                        <strong>{{ Frontend::money($product['case_price']) }}</strong>
                        <span>/ {{ __($product['pack_unit']) }} ({{ Frontend::num($product['per_pack']) }} {{ __('pcs') }})</span>
                    </div>
                @endif
            @else
                <div class="price-row ask"><i class="mdi mdi-phone"></i> {{ __('Call for price') }}</div>
            @endif
        </div>

        <div class="p-actions">
            <button type="button" class="btn btn-primary btn-sm"
                    data-add-order
                    data-id="{{ $product['id'] }}"
                    data-name="{{ $product['name'] }}"
                    data-unit="{{ $orderBy }}"
                    data-price="{{ $unitStep }}">
                <i class="mdi mdi-plus"></i> {{ __('Add to list') }}
            </button>
            <a href="{{ Frontend::waLink($business->mobile_one ?? null, __('Hello! I want the wholesale price for:') . ' ' . $product['name'] . ($product['pack_label'] ? ' (' . $product['pack_label'] . ')' : '')) }}"
               target="_blank" rel="noopener" class="btn btn-wa-soft btn-sm" aria-label="WhatsApp">
                <i class="mdi mdi-whatsapp"></i>
            </a>
        </div>
    </div>
</article>
