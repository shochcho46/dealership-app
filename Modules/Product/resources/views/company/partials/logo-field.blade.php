{{-- Company logo upload with live preview. Expects optional $company (edit page). --}}
@php
    $currentLogo = isset($company) && $company ? $company->logo_url : null;
@endphp
<div class="row">
    <div class="col-md-8">
        <div class="mb-3">
            <label for="logo" class="form-label">Company Logo</label>
            <input type="file" class="form-control @error('logo') is-invalid @enderror"
                   id="logo" name="logo" accept="image/*">
            @error('logo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">
                Any size. JPG, PNG, WEBP, GIF or SVG — a PNG with a transparent background looks best on the website brand cards.
                @if($currentLogo) Leave empty to keep the current logo. @endif
            </small>
            @if($currentLogo)
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" value="1" id="remove_logo" name="remove_logo" @checked(old('remove_logo'))>
                    <label class="form-check-label text-danger" for="remove_logo">Remove current logo</label>
                </div>
            @endif
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <div class="form-label">Preview</div>
            <div class="company-logo-preview" id="logoPreviewBox">
                <img id="logoPreview" src="{{ $currentLogo ?? '' }}" alt="Logo preview" @if(!$currentLogo) hidden @endif>
                <span id="logoPreviewEmpty" class="text-muted small" @if($currentLogo) hidden @endif>
                    <span class="mdi mdi-image-outline"></span> No logo
                </span>
            </div>
        </div>
    </div>
</div>

@push('custome-css')
<style>
    .company-logo-preview {
        width: 140px; height: 140px; border: 2px dashed #dee2e6; border-radius: 12px; background: #fff;
        display: flex; align-items: center; justify-content: center; overflow: hidden;
    }
    .company-logo-preview img { max-width: 100%; max-height: 100%; object-fit: contain; padding: 8px; }
    .company-logo-preview.is-removed img { opacity: .25; filter: grayscale(1); }
</style>
@endpush

@push('custome-js')
<script>
(function () {
    const input   = document.getElementById('logo');
    const img     = document.getElementById('logoPreview');
    const empty   = document.getElementById('logoPreviewEmpty');
    const box     = document.getElementById('logoPreviewBox');
    const remove  = document.getElementById('remove_logo');
    const current = img.getAttribute('src') || '';

    function show(src) {
        img.hidden = !src;
        empty.hidden = !!src;
        if (src) img.src = src;
    }

    input.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (file) {
            show(URL.createObjectURL(file));
            if (remove) { remove.checked = false; }
            box.classList.remove('is-removed');
        } else {
            show(current);
        }
    });

    if (remove) {
        remove.addEventListener('change', function () {
            box.classList.toggle('is-removed', this.checked);
        });
        box.classList.toggle('is-removed', remove.checked);
    }
})();
</script>
@endpush
