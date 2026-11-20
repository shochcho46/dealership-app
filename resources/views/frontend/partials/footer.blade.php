@php use App\Support\Frontend; @endphp
<footer class="site-footer">
    <svg class="footer-wave" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true"><path d="M0 40c240 40 480 40 720 0s480-40 720 0v40H0z"/></svg>
    <div class="container footer-grid">
        <div class="footer-brand">
            <a href="{{ route('home') }}" class="brand brand-light">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $companyName }}" class="brand-logo" width="44" height="44" loading="lazy">
                @else
                    <span class="brand-mark">{{ $business->brand_name ?? 'SSE' }}</span>
                @endif
                <span class="brand-text"><strong>{{ __($companyName) }}</strong><small>{{ __('Wholesale Distributor · Rangpur') }}</small></span>
            </a>
            <p>{{ __('footer_about') }}</p>
            @if($phone1)
                <a href="{{ Frontend::waLink($phone1, __('Hello! I want to know your wholesale prices.')) }}" target="_blank" rel="noopener" class="btn btn-wa btn-sm"><i class="mdi mdi-whatsapp"></i> {{ __('Chat on WhatsApp') }}</a>
            @endif
        </div>

        <div>
            <h4>{{ __('Quick Links') }}</h4>
            <ul class="footer-links">
                <li><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                <li><a href="{{ route('products.index') }}">{{ __('All Products') }}</a></li>
                <li><a href="{{ route('home') }}#how">{{ __('How to Order') }}</a></li>
                <li><a href="{{ route('contact') }}">{{ __('Contact') }}</a></li>
            </ul>
        </div>

        <div>
            <h4>{{ __('Contact Us') }}</h4>
            <ul class="footer-contact">
                @if($phone1)<li><i class="mdi mdi-phone"></i><a href="{{ Frontend::tel($phone1) }}">{{ Frontend::num($phone1) }}</a></li>@endif
                @if($phone2)<li><i class="mdi mdi-phone"></i><a href="{{ Frontend::tel($phone2) }}">{{ Frontend::num($phone2) }}</a></li>@endif
                @if($business?->email)<li><i class="mdi mdi-email-outline"></i><a href="mailto:{{ $business->email }}">{{ $business->email }}</a></li>@endif
                @if($business?->address)<li><i class="mdi mdi-map-marker-outline"></i><span>{{ __($business->address) }}</span></li>@endif
            </ul>
        </div>
    </div>
    <div class="container footer-bottom">
        <span>© {{ Frontend::num(date('Y')) }} {{ __($companyName) }}. {{ __('All rights reserved.') }}</span>
        <a href="{{ route('login') }}" class="staff-login"><i class="mdi mdi-lock-outline"></i> {{ __('Staff Login') }}</a>
    </div>
</footer>
