@php use App\Support\Frontend; @endphp
@if($phone1)
<div class="mobile-bar" aria-label="{{ __('Quick contact') }}">
    <a href="{{ Frontend::tel($phone1) }}" class="mb-call"><i class="mdi mdi-phone"></i><span>{{ __('Call') }}</span></a>
    <a href="{{ Frontend::waLink($phone1, __('Hello! I want to know your wholesale prices.')) }}" target="_blank" rel="noopener" class="mb-wa"><i class="mdi mdi-whatsapp"></i><span>{{ __('WhatsApp') }}</span></a>
    <button type="button" class="mb-list" data-order-open><i class="mdi mdi-clipboard-list-outline"></i><span>{{ __('Order list') }}</span><span class="order-count" data-order-count hidden>0</span></button>
</div>

<a href="{{ Frontend::waLink($phone1, __('Hello! I want to know your wholesale prices.')) }}" target="_blank" rel="noopener" class="wa-float" aria-label="WhatsApp">
    <i class="mdi mdi-whatsapp"></i>
</a>
@endif
