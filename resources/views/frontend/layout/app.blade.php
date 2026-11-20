@php
    use App\Support\Frontend;
    $isBn        = Frontend::isBn();
    $companyName = $business->company_name ?? config('app.name');
    $phone1      = $business->mobile_one ?? null;
    $phone2      = $business->mobile_two ?? null;
    $logoUrl     = $business && $business->hasMedia('logo') ? $business->getFirstMediaUrl('logo') : null;
    $pageTitle   = trim($__env->yieldContent('title'));
    $fullTitle   = ($pageTitle ? $pageTitle . ' | ' : '') . $companyName . ' — ' . __('Wholesale Distributor in Rangpur');
    $metaDesc    = trim($__env->yieldContent('description')) ?: __('meta_description');
    $assetVer    = '20261121b';
    $jsI18n      = [
                'added'        => __('Added to your order list'),
                'empty'        => __('Your order list is empty. Add products to get started.'),
                'hello'        => __('Hello! I would like to order:'),
                'name'         => __('Name'),
                'deliverTo'    => __('Deliver to'),
                'shopOpt'      => __('deliver_shop'),
                'homeOpt'      => __('deliver_home'),
                'place'        => __('Place'),
                'note'         => __('Note'),
                'sending'      => __('Sending…'),
                'sent'         => __('Thank you! We received your request and will call you shortly.'),
                'error'        => __('Could not send. Please call or WhatsApp us.'),
                'required'     => __('Please enter your name and mobile number.'),
                'askProduct'   => __('Hello! I want the wholesale price for:'),
                'items'        => __('items'),
            ];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="{{ $isBn ? 'lang-bn' : 'lang-en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    <meta name="theme-color" content="#0B1F3A">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $companyName }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ trim($__env->yieldContent('og_image')) ?: ($logoUrl ?: asset('frontend/v2/img/product-placeholder.svg')) }}">
    <meta name="twitter:card" content="summary_large_image">

    @if($logoUrl)
        <link rel="icon" href="{{ $logoUrl }}">
        <link rel="apple-touch-icon" href="{{ $logoUrl }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="{{ asset('frontend/v2/css/site.css') }}?v={{ $assetVer }}">

    {{-- Structured data: local business --}}
    <script type="application/ld+json">
        {!! json_encode([
            '@context'  => 'https://schema.org',
            '@type'     => 'WholesaleStore',
            'name'      => $companyName,
            'url'       => url('/'),
            'logo'      => $logoUrl,
            'image'     => $logoUrl,
            'telephone' => $phone1,
            'email'     => $business->email ?? null,
            'address'   => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $business->address ?? null,
                'addressLocality' => 'Rangpur',
                'addressCountry'  => 'BD',
            ],
            'areaServed' => 'Rangpur',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @stack('custome-css')
</head>
<body class="@yield('body_class')">
    <a class="skip-link" href="#main">{{ __('Skip to content') }}</a>

    @include('frontend.partials.header', compact('companyName', 'logoUrl', 'phone1'))

    <main id="main">
        @yield('content')
    </main>

    @include('frontend.partials.footer', compact('companyName', 'logoUrl', 'phone1', 'phone2'))
    @include('frontend.partials.order-drawer', compact('phone1'))
    @include('frontend.partials.mobile-bar', compact('phone1'))

    <script>
        window.SITE = {
            locale: @json(app()->getLocale()),
            whatsapp: @json(Frontend::waNumber($phone1)),
            inquiryUrl: @json(route('inquiry.store')),
            csrf: @json(csrf_token()),
            company: @json($companyName),
            i18n: @json($jsI18n),
        };
    </script>
    <script src="{{ asset('frontend/v2/js/site.js') }}?v={{ $assetVer }}" defer></script>
    @stack('custome-js')
</body>
</html>
