<div class="drawer-backdrop" data-order-close hidden></div>
<aside class="order-drawer" id="orderDrawer" aria-hidden="true" aria-labelledby="orderDrawerTitle" role="dialog">
    <div class="drawer-head">
        <div>
            <h3 id="orderDrawerTitle"><i class="mdi mdi-clipboard-list-outline"></i> {{ __('Your Order List') }}</h3>
            <p>{{ __('Pick quantities, then send on WhatsApp or as a request.') }}</p>
        </div>
        <button type="button" class="icon-btn" data-order-close aria-label="{{ __('Close') }}"><i class="mdi mdi-close"></i></button>
    </div>

    <div class="drawer-body">
        <ul class="order-items" data-order-items></ul>
        <p class="order-empty" data-order-empty>{{ __('Your order list is empty. Add products to get started.') }}</p>

        <div class="order-summary" data-order-summary hidden>
            <span>{{ __('Estimated total') }}</span>
            <strong data-order-total>৳0</strong>
            <small>{{ __('Final price is confirmed by phone.') }}</small>
        </div>

        <form class="order-form" data-order-form hidden novalidate>
            <h4>{{ __('Your details') }}</h4>
            <div class="field-row">
                <label><span>{{ __('Your name') }} *</span><input type="text" name="name" required maxlength="100" autocomplete="name"></label>
                <label><span>{{ __('Mobile') }} *</span><input type="tel" name="mobile" required maxlength="20" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX"></label>
            </div>
            <div class="field-row">
                <div class="deliver-field">
                    <span class="field-label">{{ __('Deliver to') }}</span>
                    <div class="deliver-pills" role="radiogroup" aria-label="{{ __('Deliver to') }}">
                        <label class="pill-opt"><input type="radio" name="delivery_type" value="shop" checked><span><i class="mdi mdi-storefront-outline"></i> {{ __('deliver_shop') }}</span></label>
                        <label class="pill-opt"><input type="radio" name="delivery_type" value="home" ><span><i class="mdi mdi-home-outline"></i> {{ __('deliver_home') }}</span></label>
                    </div>
                </div>
                <label><span>{{ __('Note') }}</span><input type="text" name="message" maxlength="2000"></label>
            </div>
            <label><span>{{ __('Place / delivery address') }}</span><textarea name="area" rows="2" maxlength="150" autocomplete="street-address" placeholder="{{ __('e.g. Jahaj Company More, Rangpur') }}"></textarea></label>
            <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
            <p class="form-status" data-order-status role="status"></p>
        </form>
    </div>

    <div class="drawer-foot" data-order-actions hidden>
        <button type="button" class="btn btn-wa btn-block" data-order-wa><i class="mdi mdi-whatsapp"></i> {{ __('Send on WhatsApp') }}</button>
        <button type="button" class="btn btn-primary btn-block" data-order-submit><i class="mdi mdi-send"></i> {{ __('Send Order Request') }}</button>
        <button type="button" class="btn-link" data-order-clear>{{ __('Clear list') }}</button>
    </div>
</aside>
<div class="toast" data-toast role="status" aria-live="polite"></div>
