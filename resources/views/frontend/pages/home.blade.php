@extends('frontend.layout.app')
@php use App\Support\Frontend; @endphp

@section('body_class', 'page-home')

@section('content')

{{-- ============ HERO (parallax) ============ --}}
<section class="hero" data-hero>
    <div class="hero-bg" aria-hidden="true">
        <div class="hero-grid" data-parallax="0.15"></div>
        <span class="blob blob-a" data-parallax="0.35"></span>
        <span class="blob blob-b" data-parallax="0.2"></span>
        <span class="blob blob-c" data-parallax="0.5"></span>
    </div>

    <div class="container hero-inner">
        <div class="hero-copy">
            <span class="eyebrow" data-reveal><i class="mdi mdi-truck-fast-outline"></i> {{ __('Wholesale Distributor · Rangpur') }}</span>
            <h1 data-reveal>
                {{ __('Genuine brands.') }}<br>
                <span class="grad-text">{{ __('Wholesale prices.') }}</span><br>
                {{ __('Delivered to your shop or home.') }}
            </h1>
            <p class="lead" data-reveal>{{ __('hero_lead') }}</p>
            <div class="hero-cta" data-reveal>
                <a href="{{ route('products.index') }}" class="btn btn-amber btn-lg"><i class="mdi mdi-storefront-outline"></i> {{ __('Browse Products') }}</a>
                @if($business?->mobile_one)
                    <a href="{{ Frontend::waLink($business->mobile_one, __('Hello! I want to know your wholesale prices.')) }}" target="_blank" rel="noopener" class="btn btn-glass btn-lg"><i class="mdi mdi-whatsapp"></i> {{ __('WhatsApp Order') }}</a>
                @endif
            </div>
            <ul class="hero-trust" data-reveal>
                <li><i class="mdi mdi-shield-check"></i> {{ __('100% genuine company stock') }}</li>
                <li><i class="mdi mdi-cash-multiple"></i> {{ __('Case-pack wholesale rates') }}</li>
                <li><i class="mdi mdi-map-marker-radius"></i> {{ __('Delivery across Rangpur') }}</li>
            </ul>
        </div>

        <div class="hero-stage" aria-hidden="true">
            <div class="stage-ring" data-depth="0.02"></div>
            <div class="stage-ring ring-2" data-depth="0.04"></div>
            @foreach($heroImages as $i => $img)
                <div class="float-card fc-{{ $i + 1 }}" data-depth="{{ [0.06, 0.1, 0.035, 0.08, 0.05, 0.12][$i] ?? 0.05 }}" data-parallax="{{ [-0.12, 0.08, -0.2, 0.14, -0.06, 0.18][$i] ?? 0.1 }}">
                    <img src="{{ $img }}" alt="" loading="{{ $i < 3 ? 'eager' : 'lazy' }}" decoding="async">
                </div>
            @endforeach
            <div class="stage-badge" data-depth="0.09">
                <strong>{{ Frontend::num($stats['products']) }}+</strong>
                <span>{{ __('products in stock') }}</span>
            </div>
        </div>
    </div>

    <a href="#stats" class="scroll-cue" aria-label="{{ __('Scroll down') }}"><span></span></a>
</section>

{{-- ============ STATS ============ --}}
<section class="stats" id="stats">
    <div class="container stats-grid">
        <div class="stat" data-reveal>
            <i class="mdi mdi-package-variant"></i>
            <strong data-count="{{ $stats['products'] }}">{{ Frontend::num($stats['products']) }}</strong><b>+</b>
            <span>{{ __('Products in stock') }}</span>
        </div>
        <div class="stat" data-reveal>
            <i class="mdi mdi-factory"></i>
            <strong data-count="{{ $stats['companies'] }}">{{ Frontend::num($stats['companies']) }}</strong>
            <span>{{ __('Trusted manufacturers') }}</span>
        </div>
        <div class="stat" data-reveal>
            <i class="mdi mdi-store-outline"></i>
            <strong data-count="{{ floor($stats['shops'] / 50) * 50 }}">{{ Frontend::num(floor($stats['shops'] / 50) * 50) }}</strong><b>+</b>
            <span>{{ __('Customers served') }}</span>
        </div>
        <div class="stat" data-reveal>
            <i class="mdi mdi-clock-fast"></i>
            <strong>{{ __('Fast') }}</strong>
            <span>{{ __('Route-wise delivery') }}</span>
        </div>
    </div>
</section>

{{-- ============ BRANDS ============ --}}
<section class="section brands" id="brands">
    <div class="marquee" aria-hidden="true">
        <div class="marquee-track">
            @for($r = 0; $r < 2; $r++)
                @foreach($companies as $c)
                    <span>{{ $c['name'] }}</span><i class="mdi mdi-star-four-points"></i>
                @endforeach
            @endfor
        </div>
    </div>

    <div class="container">
        <div class="section-head" data-reveal>
            <span class="kicker">{{ __('Our Partners') }}</span>
            <h2>{{ __('Brands we distribute') }}</h2>
            <p>{{ __('brands_lead') }}</p>
        </div>

        <div class="brand-grid">
            @foreach($companies as $c)
                <a href="{{ route('products.index', ['company' => $c['id']]) }}" class="brand-card" data-reveal data-tilt>
                    <span class="brand-img {{ $c['logo'] ? 'is-logo' : '' }}">
                        @if($c['logo'])
                            <img src="{{ $c['logo'] }}" alt="{{ $c['name'] }}" loading="lazy" decoding="async">
                        @elseif($c['image'])
                            <img src="{{ $c['image'] }}" alt="{{ $c['name'] }}" loading="lazy" decoding="async">
                        @else
                            <i class="mdi mdi-factory"></i>
                        @endif
                    </span>
                    <span class="brand-info">
                        <strong>{{ $c['name'] }}</strong>
                        <small>{{ Frontend::num($c['count']) }} {{ __('products') }}</small>
                    </span>
                    <i class="mdi mdi-arrow-top-right brand-go"></i>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ FEATURED PRODUCTS ============ --}}
@if($featured->isNotEmpty())
<section class="section featured">
    <div class="container">
        <div class="section-head split" data-reveal>
            <div>
                <span class="kicker">{{ __('Ready to ship') }}</span>
                <h2>{{ __('Fast-moving products') }}</h2>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-outline">{{ __('View all products') }} <i class="mdi mdi-arrow-right"></i></a>
        </div>
        <div class="product-grid">
            @foreach($featured as $product)
                @include('frontend.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ PARALLAX BAND ============ --}}
<section class="band" data-band>
    <div class="band-layer" data-parallax="-0.25" aria-hidden="true"></div>
    <div class="band-shapes" aria-hidden="true">
        <span data-parallax="0.3"></span><span data-parallax="-0.15"></span><span data-parallax="0.45"></span>
    </div>
    <div class="container band-inner" data-reveal>
        <i class="mdi mdi-format-quote-open band-quote"></i>
        <h2>{{ __('band_title') }}</h2>
        <p>{{ __('band_text') }}</p>
        <a href="{{ route('products.index') }}" class="btn btn-amber btn-lg">{{ __('Start your order') }} <i class="mdi mdi-arrow-right"></i></a>
    </div>
</section>

{{-- ============ WHY US ============ --}}
<section class="section why" id="why">
    <div class="container">
        <div class="section-head" data-reveal>
            <span class="kicker">{{ __('Why customers choose us') }}</span>
            <h2>{{ __('Wholesale made simple') }}</h2>
        </div>
        <div class="why-grid">
            <div class="why-card" data-reveal>
                <span class="why-icon"><i class="mdi mdi-tag-multiple-outline"></i></span>
                <h3>{{ __('Wholesale case pricing') }}</h3>
                <p>{{ __('why_1') }}</p>
            </div>
            <div class="why-card" data-reveal>
                <span class="why-icon"><i class="mdi mdi-certificate-outline"></i></span>
                <h3>{{ __('Genuine company stock') }}</h3>
                <p>{{ __('why_2') }}</p>
            </div>
            <div class="why-card" data-reveal>
                <span class="why-icon"><i class="mdi mdi-truck-delivery-outline"></i></span>
                <h3>{{ __('Route-wise delivery') }}</h3>
                <p>{{ __('why_3') }}</p>
            </div>
            <div class="why-card" data-reveal>
                <span class="why-icon"><i class="mdi mdi-whatsapp"></i></span>
                <h3>{{ __('Order in a minute') }}</h3>
                <p>{{ __('why_4') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- ============ HOW TO ORDER ============ --}}
<section class="section how" id="how">
    <div class="container">
        <div class="section-head" data-reveal>
            <span class="kicker">{{ __('3 easy steps') }}</span>
            <h2>{{ __('How to order') }}</h2>
        </div>
        <ol class="steps">
            <li data-reveal>
                <span class="step-no">{{ Frontend::num(1) }}</span>
                <i class="mdi mdi-magnify"></i>
                <h3>{{ __('Choose products') }}</h3>
                <p>{{ __('step_1') }}</p>
            </li>
            <li data-reveal>
                <span class="step-no">{{ Frontend::num(2) }}</span>
                <i class="mdi mdi-clipboard-plus-outline"></i>
                <h3>{{ __('Add to your list') }}</h3>
                <p>{{ __('step_2') }}</p>
            </li>
            <li data-reveal>
                <span class="step-no">{{ Frontend::num(3) }}</span>
                <i class="mdi mdi-send-check-outline"></i>
                <h3>{{ __('Send & relax') }}</h3>
                <p>{{ __('step_3') }}</p>
            </li>
        </ol>
    </div>
</section>

{{-- ============ CONTACT ============ --}}
@include('frontend.partials.contact-block')

@endsection
