@extends('frontend.layout.app')

@section('title', __('Contact'))
@section('description', __('contact_meta'))
@section('body_class', 'page-contact')

@section('content')
<section class="page-hero">
    <div class="page-hero-bg" data-parallax="0.3" aria-hidden="true"></div>
    <div class="container" data-reveal>
        <nav class="crumbs"><a href="{{ route('home') }}">{{ __('Home') }}</a><i class="mdi mdi-chevron-right"></i><span>{{ __('Contact') }}</span></nav>
        <h1>{{ __('Contact us') }}</h1>
        <p>{{ __('contact_lead') }}</p>
    </div>
</section>

@include('frontend.partials.contact-block', ['withForm' => true])

<section class="map-section">
    <div class="map-frame wide">
        <iframe title="{{ __('Our location on map') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                src="https://maps.google.com/maps?q={{ urlencode($business?->address ?: 'South Guptopara, Rangpur') }}&z=15&output=embed"></iframe>
    </div>
</section>
@endsection
