{{--
    "Website display" box for the product create / edit form.
    Lets the admin pick which image the public website shows and pin the product to a hero slot.

    Expects: $product (null on create), $heroHolders (slot => ['id' => .., 'name' => ..]).
    Works with the existing #product_image / #product_other_images file inputs and #name.
--}}
@php
    $isEdit       = isset($product) && $product;
    $existing     = [];
    if ($isEdit) {
        $mainMedia = $product->getFirstMedia('product_image');
        if ($mainMedia) {
            $existing[] = ['value' => 'media:' . $mainMedia->id, 'url' => $mainMedia->getUrl(), 'group' => 'main', 'label' => 'Main image'];
        }
        foreach ($product->getMedia('product_other_image') as $i => $m) {
            $existing[] = ['value' => 'media:' . $m->id, 'url' => $m->getUrl(), 'group' => 'other', 'label' => 'Other image ' . ($i + 1)];
        }
    }
    $currentChoice = $isEdit && $product->websiteMedia() ? 'media:' . $product->websiteMedia()->id : 'new_main';
    $oldChoice     = old('website_image');
    if ($oldChoice && str_starts_with($oldChoice, 'media:')) {
        $currentChoice = $oldChoice;
    }
    $heroSlotNames = [
        1 => 'Slot 1 — large card, top centre',
        2 => 'Slot 2 — left middle',
        3 => 'Slot 3 — right middle',
        4 => 'Slot 4 — bottom left',
        5 => 'Slot 5 — bottom right',
    ];
    $currentHero = old('hero_position', $isEdit ? $product->hero_position : null);
@endphp

<hr class="my-4">
<div class="website-box" id="websiteBox"
     data-existing='@json($existing)'
     data-current="{{ $currentChoice }}"
     data-placeholder="{{ asset('frontend/v2/img/product-placeholder.svg') }}"
     data-hero-holders='@json($heroHolders)'
     data-product-id="{{ $isEdit ? $product->id : '' }}">

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <h5 class="mb-0"><span class="mdi mdi-web text-success"></span> Website display</h5>
        @if($isEdit)
            @if($product->status && $product->total_available_quantity > 0)
                <a href="{{ route('products.show', [$product->id, \Illuminate\Support\Str::slug($product->name) ?: 'product']) }}"
                   target="_blank" rel="noopener" class="badge bg-success text-decoration-none">
                    <span class="mdi mdi-eye"></span> Showing on website — open
                </a>
            @else
                <span class="badge bg-secondary">
                    <span class="mdi mdi-eye-off"></span> Hidden on website ({{ $product->status ? 'no stock' : 'inactive' }})
                </span>
            @endif
        @else
            <span class="badge bg-light text-dark border">Shows on the website once Active and in stock</span>
        @endif
    </div>

    {{-- Website price: MRP and the price shown on the website. MRP - Website price = discount. --}}
    <div class="row g-3 mb-3 web-price-row">
        <div class="col-md-4">
            <label for="mrp" class="form-label">MRP <small class="text-muted">(per piece)</small></label>
            <div class="input-group">
                <span class="input-group-text">৳</span>
                <input type="number" step="0.01" min="0" class="form-control @error('mrp') is-invalid @enderror"
                       id="mrp" name="mrp" placeholder="e.g. 120" value="{{ old('mrp', $isEdit ? $product->mrp : '') }}">
                @error('mrp')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-4">
            <label for="web_price" class="form-label">Website price <small class="text-muted">(per piece)</small></label>
            <div class="input-group">
                <span class="input-group-text">৳</span>
                <input type="number" step="0.01" min="0" class="form-control @error('web_price') is-invalid @enderror"
                       id="web_price" name="web_price" placeholder="e.g. 100" value="{{ old('web_price', $isEdit ? $product->web_price : '') }}">
                @error('web_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="web-discount w-100" id="webDiscount">—</div>
        </div>
        <div class="col-12 mt-1">
            <small class="text-muted">Price for <b>one piece</b>. The website shows the Website price with the MRP crossed out; the difference is the discount.
                The case/pack price is calculated automatically. Leave Website price empty to use the stock sell price.</small>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <label class="form-label fw-semibold mb-1">Which photo should customers see?</label>
            <p class="text-muted small mb-2">
                Tick one. It is used on the website product card, as the first photo on the product page, and in the hero banner.
                Choosing new files above adds them here; images being replaced turn grey.
            </p>
            <div class="web-pick-grid" id="webPickGrid"></div>
            <p class="text-muted small mb-0 d-none" id="webPickEmpty">
                <span class="mdi mdi-image-off-outline"></span>
                No image yet — upload a Product Thumbnail above. Until then the website shows a placeholder.
            </p>
            @error('website_image')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror

            <div class="mt-4">
                <label for="hero_position" class="form-label fw-semibold mb-1">
                    Show in website hero (top banner)
                </label>
                <select name="hero_position" id="hero_position" class="form-select @error('hero_position') is-invalid @enderror" style="max-width: 420px">
                    <option value="">No — not pinned</option>
                    @foreach($heroSlotNames as $slot => $label)
                        @php $holder = $heroHolders[$slot] ?? null; @endphp
                        <option value="{{ $slot }}" @selected((string) $currentHero === (string) $slot)>
                            {{ $label }}@if($holder && (!$isEdit || $holder['id'] !== $product->id)) (now: {{ \Illuminate\Support\Str::limit($holder['name'], 28) }})@endif
                        </option>
                    @endforeach
                </select>
                @error('hero_position')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text" id="heroNote">
                    The hero has 5 floating photos. Empty slots are filled automatically.
                    @can('website_settings')
                        See all slots on <a href="{{ route('website-hero.index') }}" target="_blank">Website → Hero Images</a>.
                    @endcan
                </div>
                <div class="small text-warning-emphasis mt-1 d-none" id="heroReplace"></div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="text-muted small mb-1">Website card preview</div>
            <div class="web-card-preview">
                <div class="wcp-media"><img id="wcpImg" src="{{ asset('frontend/v2/img/product-placeholder.svg') }}" alt=""></div>
                <div class="wcp-body">
                    <div class="wcp-name" id="wcpName">{{ $isEdit ? $product->name : 'Product name' }}</div>
                    <div class="wcp-hint"><span class="mdi mdi-check-circle"></span> This photo shows on the website</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('custome-css')
<style>
    .web-discount { border: 1px dashed #ced4da; border-radius: 8px; padding: 7px 10px; font-weight: 600; color: #6c757d; background: #f8f9fa; }
    .web-discount.ok { color: #0f5132; background: #d1e7dd; border-color: #a3cfbb; }
    .web-discount.bad { color: #842029; background: #f8d7da; border-color: #f1aeb5; }
</style>
<style>
    .website-box { background: #f6fbfa; border: 1px solid #d5ece9; border-radius: 12px; padding: 18px; }
    .web-pick-grid { display: flex; flex-wrap: wrap; gap: 12px; }
    .web-pick { position: relative; width: 112px; margin: 0; cursor: pointer; text-align: center; }
    .web-pick input { position: absolute; opacity: 0; pointer-events: none; }
    .web-pick img { width: 112px; height: 112px; object-fit: cover; border-radius: 10px; border: 3px solid #dee2e6; background: #fff; transition: border-color .15s, box-shadow .15s; }
    .web-pick input:checked + img { border-color: #0FB5A6; box-shadow: 0 0 0 3px rgba(15,181,166,.25); }
    .web-pick input:focus-visible + img { outline: 2px solid #0B1F3A; outline-offset: 2px; }
    .web-pick .wp-check { position: absolute; top: 6px; right: 6px; width: 24px; height: 24px; border-radius: 50%; background: #0FB5A6; color: #fff; display: none; align-items: center; justify-content: center; }
    .web-pick input:checked ~ .wp-check { display: flex; }
    .web-pick .wp-label { display: block; font-size: 12px; color: #495057; margin-top: 4px; line-height: 1.2; }
    .web-pick .wp-tag { display: inline-block; font-size: 10px; font-weight: 600; padding: 1px 6px; border-radius: 6px; margin-top: 2px; }
    .web-pick .wp-tag.new { background: #e7f1ff; color: #0b5ed7; }
    .web-pick .wp-tag.live { background: #d1f2ee; color: #0a7d72; }
    .web-pick.is-replaced { cursor: not-allowed; }
    .web-pick.is-replaced img { opacity: .35; filter: grayscale(1); }
    .web-pick.is-replaced .wp-label { text-decoration: line-through; color: #adb5bd; }
    .web-card-preview { width: 220px; max-width: 100%; border-radius: 16px; overflow: hidden; background: #fff; box-shadow: 0 10px 30px rgba(11,31,58,.12); border: 1px solid #e9ecef; }
    .web-card-preview .wcp-media { aspect-ratio: 1/1; background: linear-gradient(160deg, #eef6f5, #fff); display: flex; align-items: center; justify-content: center; }
    .web-card-preview .wcp-media img { width: 100%; height: 100%; object-fit: contain; padding: 10px; }
    .web-card-preview .wcp-body { padding: 10px 12px 12px; }
    .web-card-preview .wcp-name { font-weight: 700; color: #0B1F3A; font-size: 14px; line-height: 1.3; }
    .web-card-preview .wcp-hint { font-size: 11px; color: #0a7d72; margin-top: 6px; }
</style>
@endpush

@push('custome-js')
<script>
(function () {
    var mrp = document.getElementById('mrp'), web = document.getElementById('web_price'), out = document.getElementById('webDiscount');
    if (!mrp || !web || !out) return;
    function show() {
        var m = parseFloat(mrp.value), w = parseFloat(web.value);
        out.className = 'web-discount w-100';
        if (!(m > 0) || !(w > 0)) { out.textContent = m > 0 ? 'MRP ৳' + m + ' (no website price)' : '—'; return; }
        if (w > m) { out.classList.add('bad'); out.textContent = 'Website price is higher than MRP'; return; }
        if (w === m) { out.textContent = 'No discount'; return; }
        out.classList.add('ok');
        out.textContent = 'Discount: ৳' + (m - w).toFixed(2).replace(/\.00$/, '') + ' (' + Math.round((m - w) / m * 100) + '% off)';
    }
    mrp.addEventListener('input', show); web.addEventListener('input', show); show();
})();
</script>
<script>
(function () {
    const box = document.getElementById('websiteBox');
    if (!box) return;

    const existing    = JSON.parse(box.dataset.existing || '[]');
    const holders     = JSON.parse(box.dataset.heroHolders || '{}');
    const productId   = box.dataset.productId ? parseInt(box.dataset.productId, 10) : null;
    const placeholder = box.dataset.placeholder;
    const grid        = document.getElementById('webPickGrid');
    const emptyMsg    = document.getElementById('webPickEmpty');
    const previewImg  = document.getElementById('wcpImg');
    const previewName = document.getElementById('wcpName');
    const mainInput   = document.getElementById('product_image');
    const otherInput  = document.getElementById('product_other_images');
    const nameInput   = document.getElementById('name');
    const heroSelect  = document.getElementById('hero_position');
    const heroReplace = document.getElementById('heroReplace');

    let selected  = box.dataset.current || 'new_main';
    let newMain   = null;   // {url}
    let newOthers = [];     // [{url}]

    function urlFor(file) { return URL.createObjectURL(file); }

    function options() {
        const list = [];
        existing.forEach(function (img) {
            const replaced = (img.group === 'main' && newMain) || (img.group === 'other' && newOthers.length > 0);
            list.push({ value: img.value, url: img.url, label: img.label, replaced: replaced, isNew: false });
        });
        if (newMain) list.push({ value: 'new_main', url: newMain.url, label: 'New thumbnail', replaced: false, isNew: true });
        newOthers.forEach(function (f, i) {
            list.push({ value: 'new_other:' + i, url: f.url, label: 'New image ' + (i + 1), replaced: false, isNew: true });
        });
        return list;
    }

    function render() {
        const list = options();
        const usable = list.filter(function (o) { return !o.replaced; });

        if (!usable.some(function (o) { return o.value === selected; })) {
            selected = usable.length ? usable[0].value : '';
        }

        grid.innerHTML = '';
        list.forEach(function (o) {
            const label = document.createElement('label');
            label.className = 'web-pick' + (o.replaced ? ' is-replaced' : '');
            label.title = o.replaced ? 'This image will be replaced by the new upload' : 'Use this photo on the website';

            const input = document.createElement('input');
            input.type = 'radio';
            input.name = 'website_image';
            input.value = o.value;
            input.disabled = o.replaced;
            input.checked = o.value === selected;
            input.addEventListener('change', function () { selected = o.value; updatePreview(); });

            const img = document.createElement('img');
            img.src = o.url;
            img.alt = o.label;

            const check = document.createElement('span');
            check.className = 'wp-check';
            check.innerHTML = '<span class="mdi mdi-check"></span>';

            const text = document.createElement('span');
            text.className = 'wp-label';
            text.textContent = o.replaced ? o.label + ' (will be replaced)' : o.label;

            label.append(input, img, check, text);
            if (o.isNew) {
                const tag = document.createElement('span');
                tag.className = 'wp-tag new';
                tag.textContent = 'NEW';
                label.append(tag);
            } else if (!o.replaced && o.value === box.dataset.current) {
                const tag = document.createElement('span');
                tag.className = 'wp-tag live';
                tag.textContent = '✓ On website now';
                label.append(tag);
            }
            grid.append(label);
        });

        emptyMsg.classList.toggle('d-none', list.length > 0);
        updatePreview();
    }

    function updatePreview() {
        const chosen = options().find(function (o) { return o.value === selected && !o.replaced; });
        previewImg.src = chosen ? chosen.url : placeholder;
    }

    if (mainInput) {
        mainInput.addEventListener('change', function () {
            const f = this.files && this.files[0];
            newMain = f ? { url: urlFor(f) } : null;
            if (newMain && (selected === '' || selected.indexOf('media:') === 0 && existing.some(function (e) { return e.group === 'main' && e.value === selected; }))) {
                selected = 'new_main';
            }
            render();
        });
    }
    if (otherInput) {
        otherInput.addEventListener('change', function () {
            const files = Array.prototype.slice.call(this.files || [], 0, 6);
            newOthers = files.map(function (f) { return { url: urlFor(f) }; });
            render();
        });
    }
    if (nameInput) {
        nameInput.addEventListener('input', function () {
            previewName.textContent = this.value.trim() || 'Product name';
        });
    }

    function heroNote() {
        const slot = heroSelect.value;
        const holder = slot ? holders[slot] : null;
        if (holder && holder.id !== productId) {
            heroReplace.textContent = '⚠ Saving will replace "' + holder.name + '" in this slot.';
            heroReplace.classList.remove('d-none');
        } else {
            heroReplace.classList.add('d-none');
        }
    }
    if (heroSelect) heroSelect.addEventListener('change', heroNote);

    render();
    if (heroSelect) heroNote();
})();
</script>
@endpush
