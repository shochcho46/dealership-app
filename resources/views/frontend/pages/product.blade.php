@extends('frontend.layout.app')
@php
    use App\Support\Frontend;
    // Website orders are placed in pieces (quantity), priced per piece.
    $orderBy  = __('pcs');
    $unitStep = $product['price_piece'] ?? 0;
    $waText   = __('Hello! I want the wholesale price for:') . ' ' . $product['name'] . ($product['pack_label'] ? ' (' . $product['pack_label'] . ')' : '');
@endphp

@section('title', $product['name'])
@section('description', $product['name'] . ' — ' . $product['company'] . ($product['pack_label'] ? ', ' . $product['pack_label'] : '') . '. ' . __('Wholesale supply in Rangpur.'))
@section('og_image', $product['has_image'] ? $product['image'] : '')
@section('body_class', 'page-product')

@push('custome-css')
<script type="application/ld+json">
    {!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type'    => 'Product',
        'name'     => $product['name'],
        'image'    => $product['has_image'] ? $product['image'] : null,
        'brand'    => ['@type' => 'Brand', 'name' => $product['company']],
        'offers'   => $product['price_piece'] ? [
            '@type'         => 'Offer',
            'priceCurrency' => 'BDT',
            'price'         => $product['price_piece'],
            'availability'  => 'https://schema.org/InStock',
            'url'           => url()->current(),
        ] : null,
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<section class="section product-detail">
    <div class="container">
        <nav class="crumbs dark">
            <a href="{{ route('home') }}">{{ __('Home') }}</a><i class="mdi mdi-chevron-right"></i>
            <a href="{{ route('products.index') }}">{{ __('Products') }}</a><i class="mdi mdi-chevron-right"></i>
            <a href="{{ route('products.index', ['company' => $product['company_id']]) }}">{{ $product['company'] }}</a>
        </nav>

        <div class="pd-grid">
            <div class="pd-gallery" data-reveal>
                <div class="pd-main {{ $product['has_image'] ? '' : 'is-placeholder' }}">
                    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" data-gallery-main decoding="async">
                    <span class="badge-stock"><i class="mdi mdi-check-circle"></i> {{ __('In stock') }}</span>
                </div>
                @if(count($product['gallery']) > 1)
                    <div class="pd-thumbs">
                        @foreach($product['gallery'] as $i => $img)
                            <button type="button" class="{{ $i === 0 ? 'on' : '' }}" data-gallery-thumb="{{ $img }}" aria-label="{{ __('Image') }} {{ $i + 1 }}">
                                <img src="{{ $img }}" alt="" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="pd-info" data-reveal>
                <a href="{{ route('products.index', ['company' => $product['company_id']]) }}" class="p-company">{{ $product['company'] }}</a>
                <h1>{{ $product['name'] }}</h1>

                <div class="pd-price">
                    @if($product['price_piece'])
                        <div class="pd-price-box">
                            <small>{{ __('Single price') }}</small>
                            <strong>{{ Frontend::money($product['price_piece']) }}</strong>
                            <span>/ {{ __($product['unit_label']) }}</span>
                            @if(!empty($product['mrp_piece']))
                                <div class="pd-mrp">
                                    {{ __('MRP') }} <del>{{ Frontend::money($product['mrp_piece']) }}</del>
                                    <b>-{{ Frontend::num($product['discount_pct']) }}% {{ __('off') }}</b>
                                </div>
                                <em class="pd-save">{{ __('You save') }} {{ Frontend::money($product['mrp_piece'] - $product['price_piece']) }}</em>
                            @endif
                        </div>
                        @if($product['case_price'])
                            <div class="pd-price-box case">
                                <small>{{ __('Case price') }}</small>
                                <strong>{{ Frontend::money($product['case_price']) }}</strong>
                                <span>/ {{ __($product['pack_unit']) }} ({{ Frontend::num($product['per_pack']) }} {{ __('pcs') }})</span>
                                @if(!empty($product['case_mrp']))
                                    <div class="pd-mrp">{{ __('MRP') }} <del>{{ Frontend::money($product['case_mrp']) }}</del></div>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="pd-price-box ask">
                            <small>{{ __('Wholesale price') }}</small>
                            <strong>{{ __('Call for price') }}</strong>
                        </div>
                    @endif
                </div>
                <p class="pd-note"><i class="mdi mdi-information-outline"></i> {{ __('Prices may change with company rates. Final price is confirmed by phone.') }}</p>

                <dl class="pd-specs">
                    <div><dt>{{ __('Brand / Company') }}</dt><dd>{{ $product['company'] }}</dd></div>
                    @if($product['pack_label'])<div><dt>{{ __('Pack size') }}</dt><dd>{{ Frontend::num($product['pack_label']) }}</dd></div>@endif
                    <div><dt>{{ __('Sold by') }}</dt><dd>{{ __($orderBy) }}</dd></div>
                    <div><dt>{{ __('Availability') }}</dt><dd class="ok"><i class="mdi mdi-check-circle"></i> {{ __('In stock') }}</dd></div>
                </dl>

                @if($product['description'])
                    <div class="pd-desc">{!! nl2br(e($product['description'])) !!}</div>
                @endif

                <div class="pd-order">
                    <div class="qty" data-qty>
                        <button type="button" data-qty-dec aria-label="{{ __('Less') }}"><i class="mdi mdi-minus"></i></button>
                        <input type="number" min="1" max="100000" value="1" data-qty-input aria-label="{{ __('Quantity') }}">
                        <button type="button" data-qty-inc aria-label="{{ __('More') }}"><i class="mdi mdi-plus"></i></button>
                        <span class="qty-unit">{{ __('pcs') }}</span>
                    </div>
                    @if($product['per_pack'])
                        <small class="qty-hint">1 {{ __($product['pack_unit']) }} = {{ Frontend::num($product['per_pack']) }} {{ __('pcs') }}</small>
                    @endif
                    <button type="button" class="btn btn-primary btn-lg"
                            data-add-order data-with-qty
                            data-id="{{ $product['id'] }}"
                            data-name="{{ $product['name'] }}"
                            data-unit="{{ $orderBy }}"
                            data-price="{{ $unitStep }}">
                        <i class="mdi mdi-clipboard-plus-outline"></i> {{ __('Add to order list') }}
                    </button>
                </div>

                <div class="pd-contact">
                    <a href="{{ Frontend::waLink($business->mobile_one ?? null, $waText) }}" target="_blank" rel="noopener" class="btn btn-wa"><i class="mdi mdi-whatsapp"></i> {{ __('WhatsApp this product') }}</a>
                    @if($business?->mobile_one)
                        <a href="{{ Frontend::tel($business->mobile_one) }}" class="btn btn-outline"><i class="mdi mdi-phone"></i> {{ __('Call') }} {{ Frontend::num($business->mobile_one) }}</a>
                    @endif
                    <a href="{{ route('contact', ['product' => $product['id']]) }}#contact" class="btn-link"><i class="mdi mdi-message-text-outline"></i> {{ __('Send a written request') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>

@if($related->isNotEmpty())
<section class="section related">
    <div class="container">
        <div class="section-head split" data-reveal>
            <div>
                <span class="kicker">{{ $product['company'] }}</span>
                <h2>{{ __('More from this brand') }}</h2>
            </div>
            <a href="{{ route('products.index', ['company' => $product['company_id']]) }}" class="btn btn-outline">{{ __('View all') }} <i class="mdi mdi-arrow-right"></i></a>
        </div>
        <div class="product-grid">
            @foreach($related as $item)
                @include('frontend.partials.product-card', ['product' => $item])
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
