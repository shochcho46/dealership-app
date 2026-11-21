{{-- SR commission price (per piece). Used by Financial → Commission. Empty = use the stock sell price. --}}
@php
    $stockSell = $product ? optional($product->stocks()->orderByDesc('sell_price')->first())->sell_price : null;
@endphp
<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="commission_amount" class="form-label">
                <span class="mdi mdi-hand-coin-outline text-success"></span> Commission price <small class="text-muted">(per piece)</small>
            </label>
            <div class="input-group">
                <span class="input-group-text">৳</span>
                <input type="number" step="any" min="0" class="form-control @error('commission_amount') is-invalid @enderror"
                       id="commission_amount" name="commission_amount" placeholder="Empty = stock sell price"
                       value="{{ old('commission_amount', $product && $product->commission_amount !== null ? rtrim(rtrim($product->commission_amount, '0'), '.') : '') }}">
                @error('commission_amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <small class="form-text text-muted">
                SR commission is calculated against this price: sold above it = plus, below it (or discount) = minus.
                Leave empty to use the stock sell price{{ $stockSell !== null ? ' (now ৳' . number_format($stockSell, 2) . ')' : '' }}.
            </small>
        </div>
    </div>
</div>
