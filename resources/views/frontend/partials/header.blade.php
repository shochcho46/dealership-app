@php use App\Support\Frontend; @endphp
<header class="site-header" data-header>
    <div class="container header-inner">
        <a href="{{ route('home') }}" class="brand" aria-label="{{ $companyName }}">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $companyName }}" class="brand-logo" width="44" height="44">
            @else
                <span class="brand-mark">{{ $business->brand_name ?? 'SSE' }}</span>
            @endif
            <span class="brand-text">
                <strong>{{ __($companyName) }}</strong>
                <small>{{ __('Wholesale Distributor · Rangpur') }}</small>
            </span>
        </a>

        <nav class="main-nav" id="mainNav" aria-label="{{ __('Main menu') }}">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">{{ __('Home') }}</a>
            <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">{{ __('Products') }}</a>
            <a href="{{ route('home') }}#brands">{{ __('Brands') }}</a>
            <a href="{{ route('home') }}#how">{{ __('How to Order') }}</a>
            <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">{{ __('Contact') }}</a>
            <div class="nav-mobile-extra">
                @if($phone1)
                    <a href="{{ Frontend::tel($phone1) }}" class="btn btn-amber btn-block"><i class="mdi mdi-phone"></i> {{ Frontend::num($phone1) }}</a>
                @endif
            </div>
        </nav>

        <div class="header-actions">
            <div class="lang-switch" role="group" aria-label="Language">
                <a href="{{ route('lang.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'on' : '' }}" hreflang="en">EN</a>
                <a href="{{ route('lang.switch', 'bn') }}" class="{{ app()->getLocale() === 'bn' ? 'on' : '' }}" hreflang="bn">বাংলা</a>
            </div>
            <button type="button" class="icon-btn order-toggle" data-order-open aria-label="{{ __('Order list') }}">
                <i class="mdi mdi-clipboard-list-outline"></i>
                <span class="order-count" data-order-count hidden>0</span>
            </button>
            @if($phone1)
                <a href="{{ Frontend::tel($phone1) }}" class="btn btn-amber btn-sm header-call"><i class="mdi mdi-phone-in-talk"></i> <span>{{ __('Call Now') }}</span></a>
            @endif
            <button type="button" class="icon-btn nav-toggle" data-nav-toggle aria-controls="mainNav" aria-expanded="false" aria-label="{{ __('Menu') }}">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>
