<form method="POST" action="{{ route('inquiry.store') }}" class="inquiry-form">
    @csrf
    <h3><i class="mdi mdi-message-text-outline"></i> {{ __('Send us a request') }}</h3>
    <p class="muted">{{ __('We usually call back within a few hours.') }}</p>

    @if(session('inquiry_success'))
        <div class="alert ok"><i class="mdi mdi-check-circle"></i> {{ session('inquiry_success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert err"><i class="mdi mdi-alert-circle"></i> {{ $errors->first() }}</div>
    @endif

    @if(!empty($inquiryProduct))
        <input type="hidden" name="product_id" value="{{ $inquiryProduct['id'] }}">
        <div class="alert info"><i class="mdi mdi-package-variant"></i> {{ __('About') }}: <strong>{{ $inquiryProduct['name'] }}</strong></div>
    @endif

    <div class="field-row">
        <label><span>{{ __('Your name') }} *</span><input type="text" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name"></label>
        <label><span>{{ __('Mobile') }} *</span><input type="tel" name="mobile" value="{{ old('mobile') }}" required maxlength="20" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX"></label>
    </div>
    <div class="field-row">
        <div class="deliver-field">
            <span class="field-label">{{ __('Deliver to') }}</span>
            <div class="deliver-pills" role="radiogroup" aria-label="{{ __('Deliver to') }}">
                <label class="pill-opt"><input type="radio" name="delivery_type" value="shop" @checked(old('delivery_type', 'shop') !== 'home')><span><i class="mdi mdi-storefront-outline"></i> {{ __('deliver_shop') }}</span></label>
                <label class="pill-opt"><input type="radio" name="delivery_type" value="home" @checked(old('delivery_type') === 'home')><span><i class="mdi mdi-home-outline"></i> {{ __('deliver_home') }}</span></label>
            </div>
        </div>
        <label><span>{{ __('Place / delivery address') }}</span><input type="text" name="area" value="{{ old('area') }}" maxlength="150" autocomplete="street-address" placeholder="{{ __('e.g. Jahaj Company More, Rangpur') }}"></label>
    </div>
    <label><span>{{ __('What do you need?') }}</span><textarea name="message" rows="4" maxlength="2000" placeholder="{{ __('e.g. 10 cases of drinking water 500ml') }}">{{ old('message') }}</textarea></label>
    <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="mdi mdi-send"></i> {{ __('Send Request') }}</button>
</form>
