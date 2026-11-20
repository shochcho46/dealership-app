@extends('frontend.layout.app')
@php use App\Support\Frontend; @endphp

@section('title', $activeCompany ? $activeCompany['name'] : __('Products'))
@section('description', __('products_meta'))
@section('body_class', 'page-products')

@section('content')
<section class="page-hero">
    <div class="page-hero-bg" data-parallax="0.3" aria-hidden="true"></div>
    <div class="container" data-reveal>
        <nav class="crumbs"><a href="{{ route('home') }}">{{ __('Home') }}</a><i class="mdi mdi-chevron-right"></i><span>{{ __('Products') }}</span></nav>
        <h1>{{ $activeCompany ? $activeCompany['name'] : __('Wholesale product catalog') }}</h1>
        <p>{{ __('catalog_lead') }}</p>

        <form method="GET" action="{{ route('products.index') }}" class="hero-search" role="search">
            @if($activeCompany)<input type="hidden" name="company" value="{{ $activeCompany['id'] }}">@endif
            <input type="hidden" name="sort" value="{{ $sort }}">
            <i class="mdi mdi-magnify"></i>
            <input type="search" name="q" value="{{ $q }}" placeholder="{{ __('Search products, e.g. water, oil, tissue…') }}" aria-label="{{ __('Search products') }}">
            <button type="submit" class="btn btn-amber">{{ __('Search') }}</button>
        </form>
    </div>
</section>

<section class="section catalog">
    <div class="container catalog-layout">
        {{-- Filters --}}
        <aside class="filters" id="filters" data-filters>
            <div class="filters-head">
                <h3><i class="mdi mdi-filter-variant"></i> {{ __('Filter by brand') }}</h3>
                <button type="button" class="icon-btn filters-close" data-filters-close aria-label="{{ __('Close') }}"><i class="mdi mdi-close"></i></button>
            </div>
            <div class="chip-list">
                <a href="{{ route('products.index', array_filter(['q' => $q, 'sort' => $sort !== 'name' ? $sort : null])) }}" class="chip {{ $activeCompany ? '' : 'on' }}">
                    {{ __('All brands') }} <b>{{ Frontend::num($companies->sum('count')) }}</b>
                </a>
                @foreach($companies as $c)
                    <a href="{{ route('products.index', array_filter(['company' => $c['id'], 'q' => $q, 'sort' => $sort !== 'name' ? $sort : null])) }}"
                       class="chip {{ $activeCompany && $activeCompany['id'] === $c['id'] ? 'on' : '' }}">
                        @if($c['logo'])<img src="{{ $c['logo'] }}" alt="" class="chip-logo" loading="lazy">@endif
                        {{ $c['name'] }} <b>{{ Frontend::num($c['count']) }}</b>
                    </a>
                @endforeach
            </div>

            <div class="filter-help">
                <i class="mdi mdi-headset"></i>
                <div>
                    <strong>{{ __("Can't find a product?") }}</strong>
                    <p>{{ __('Call us — we can arrange it from the company.') }}</p>
                    @if($business?->mobile_one)
                        <a href="{{ Frontend::tel($business->mobile_one) }}">{{ Frontend::num($business->mobile_one) }}</a>
                    @endif
                </div>
            </div>
        </aside>

        <div class="catalog-main">
            <div class="catalog-bar">
                <p>
                    <strong>{{ Frontend::num($products->total()) }}</strong> {{ __('products found') }}
                    @if($q)<span class="q-tag">“{{ $q }}” <a href="{{ route('products.index', array_filter(['company' => $activeCompany['id'] ?? null, 'sort' => $sort !== 'name' ? $sort : null])) }}" aria-label="{{ __('Clear search') }}"><i class="mdi mdi-close"></i></a></span>@endif
                </p>
                <div class="bar-actions">
                    <button type="button" class="btn btn-outline btn-sm filters-open" data-filters-open><i class="mdi mdi-filter-variant"></i> {{ __('Brands') }}</button>
                    <form method="GET" action="{{ route('products.index') }}" class="sort-form">
                        @if($activeCompany)<input type="hidden" name="company" value="{{ $activeCompany['id'] }}">@endif
                        @if($q)<input type="hidden" name="q" value="{{ $q }}">@endif
                        <label class="sr-only" for="sort">{{ __('Sort') }}</label>
                        <select name="sort" id="sort" onchange="this.form.submit()">
                            <option value="name" @selected($sort === 'name')>{{ __('Name A–Z') }}</option>
                            <option value="price_asc" @selected($sort === 'price_asc')>{{ __('Price: low to high') }}</option>
                            <option value="price_desc" @selected($sort === 'price_desc')>{{ __('Price: high to low') }}</option>
                            <option value="newest" @selected($sort === 'newest')>{{ __('Newest') }}</option>
                        </select>
                    </form>
                </div>
            </div>

            @if($products->isEmpty())
                <div class="empty-state" data-reveal>
                    <i class="mdi mdi-package-variant-remove"></i>
                    <h3>{{ __('No products found') }}</h3>
                    <p>{{ __('Try another search, or ask us directly — we will help.') }}</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary">{{ __('See all products') }}</a>
                </div>
            @else
                <div class="product-grid">
                    @foreach($products as $product)
                        @include('frontend.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
                {{ $products->onEachSide(1)->links('frontend.partials.pagination') }}
            @endif
        </div>
    </div>
</section>
<div class="drawer-backdrop filters-backdrop" data-filters-close hidden></div>
@endsection
