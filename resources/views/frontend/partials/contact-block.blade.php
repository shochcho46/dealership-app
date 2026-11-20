@php
    use App\Support\Frontend;
    $mapQuery = $business?->address ?: 'South Guptopara, Rangpur';
    $withForm = $withForm ?? false;
@endphp
<section class="section contact" id="contact">
    <div class="container">
        <div class="section-head" data-reveal>
            <span class="kicker">{{ __('Get in touch') }}</span>
            <h2>{{ __('Talk to our sales team') }}</h2>
            <p>{{ __('contact_lead') }}</p>
        </div>

        <div class="contact-grid">
            <div class="contact-cards">
                @if($business?->mobile_one)
                    <a href="{{ Frontend::tel($business->mobile_one) }}" class="c-card" data-reveal>
                        <span class="c-icon call"><i class="mdi mdi-phone-in-talk"></i></span>
                        <span><small>{{ __('Call sales') }}</small><strong>{{ Frontend::num($business->mobile_one) }}</strong></span>
                    </a>
                    <a href="{{ Frontend::waLink($business->mobile_one, __('Hello! I want to know your wholesale prices.')) }}" target="_blank" rel="noopener" class="c-card" data-reveal>
                        <span class="c-icon wa"><i class="mdi mdi-whatsapp"></i></span>
                        <span><small>{{ __('WhatsApp') }}</small><strong>{{ __('Chat with us') }}</strong></span>
                    </a>
                @endif
                @if($business?->mobile_two)
                    <a href="{{ Frontend::tel($business->mobile_two) }}" class="c-card" data-reveal>
                        <span class="c-icon call2"><i class="mdi mdi-phone"></i></span>
                        <span><small>{{ __('Hotline') }}</small><strong>{{ Frontend::num($business->mobile_two) }}</strong></span>
                    </a>
                @endif
                @if($business?->email)
                    <a href="mailto:{{ $business->email }}" class="c-card" data-reveal>
                        <span class="c-icon mail"><i class="mdi mdi-email-outline"></i></span>
                        <span><small>{{ __('Email') }}</small><strong class="break">{{ $business->email }}</strong></span>
                    </a>
                @endif
                @if($business?->address)
                    <div class="c-card wide" data-reveal>
                        <span class="c-icon map"><i class="mdi mdi-map-marker-radius-outline"></i></span>
                        <span><small>{{ __('Office & warehouse') }}</small><strong>{{ __($business->address) }}</strong></span>
                    </div>
                @endif
            </div>

            <div class="contact-side" data-reveal>
                @if($withForm)
                    @include('frontend.partials.inquiry-form')
                @else
                    <div class="map-frame">
                        <iframe title="{{ __('Our location on map') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                                src="https://maps.google.com/maps?q={{ urlencode($mapQuery) }}&z=15&output=embed"></iframe>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
