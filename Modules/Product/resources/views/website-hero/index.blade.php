@extends('layouts.app')

@php
    $slotNames = [
        1 => 'Large card — top centre',
        2 => 'Left middle',
        3 => 'Right middle',
        4 => 'Bottom left',
        5 => 'Bottom right',
    ];
@endphp

@push('custome-css')
<style>
    /* Mini copy of the website hero so the admin sees where each slot appears */
    .hero-mock { position: relative; border-radius: 16px; overflow: hidden; min-height: 340px;
        background: radial-gradient(circle at 75% 40%, rgba(15,181,166,.35), transparent 55%), linear-gradient(135deg, #0B1F3A, #12345e); color: #fff; }
    .hero-mock .hm-copy { position: absolute; left: 24px; top: 50%; transform: translateY(-50%); width: 38%; }
    .hero-mock .hm-copy .hm-line { height: 14px; border-radius: 7px; background: rgba(255,255,255,.25); margin-bottom: 10px; }
    .hero-mock .hm-copy .hm-line.big { height: 22px; background: rgba(255,255,255,.55); }
    .hero-mock .hm-copy .hm-btn { display: inline-block; width: 90px; height: 26px; border-radius: 13px; background: #FFB547; margin-top: 8px; }
    .hero-mock .hm-stage { position: absolute; right: 3%; top: 6%; bottom: 6%; width: 52%; }
    .hm-card { position: absolute; width: 22%; aspect-ratio: 1/1; background: rgba(255,255,255,.95); border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0,0,0,.35); display: flex; align-items: center; justify-content: center; }
    .hm-card img { width: 86%; height: 86%; object-fit: contain; }
    .hm-card .hm-num { position: absolute; top: -10px; left: -10px; width: 26px; height: 26px; border-radius: 50%;
        background: #FFB547; color: #0B1F3A; font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 13px; }
    .hm-card.auto { outline: 2px dashed rgba(255,255,255,.6); outline-offset: 3px; }
    .hm-1 { top: 0; left: 34%; width: 27%; }
    .hm-2 { top: 34%; left: 2%; }
    .hm-3 { top: 30%; right: 2%; }
    .hm-4 { bottom: 0; left: 18%; }
    .hm-5 { bottom: 4%; right: 20%; }
    .slot-thumb { width: 56px; height: 56px; object-fit: contain; background: #f4f7f7; border-radius: 8px; border: 1px solid #e3e8e8; }
    .s2-opt { display: flex; align-items: center; gap: 8px; }
    .s2-opt img { width: 34px; height: 34px; object-fit: contain; background: #f4f7f7; border-radius: 6px; }
    .s2-opt small { color: #6c757d; }
    .select2-container .select2-selection--single { height: 46px; display: flex; align-items: center; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 44px; }
    @media (max-width: 767px) {
        .hero-mock { min-height: 260px; }
        .hero-mock .hm-copy { display: none; }
        .hero-mock .hm-stage { left: 4%; right: 4%; width: auto; }
    }
</style>
@endpush

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Website Hero Images</h3></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Hero Images</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-primary card-outline mb-4">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <div class="card-title mb-0"><span class="mdi mdi-image-multiple"></span> What the home page banner shows now</div>
                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary ms-auto">
                    <span class="mdi mdi-open-in-new"></span> Open website
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error') || $errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') ?: $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="hero-mock mb-3" aria-label="Preview of the website hero">
                    <div class="hm-copy">
                        <div class="hm-line big" style="width: 90%"></div>
                        <div class="hm-line big" style="width: 70%"></div>
                        <div class="hm-line" style="width: 95%"></div>
                        <div class="hm-line" style="width: 80%"></div>
                        <span class="hm-btn"></span>
                    </div>
                    <div class="hm-stage">
                        @foreach($slotNames as $slot => $name)
                            @php $s = $shown->get($slot); @endphp
                            <div class="hm-card hm-{{ $slot }} {{ $s && !$s['pinned'] ? 'auto' : '' }}" title="Slot {{ $slot }}: {{ $s['product']['name'] ?? 'empty' }}">
                                <span class="hm-num">{{ $slot }}</span>
                                @if($s)
                                    <img src="{{ $s['product']['image'] }}" alt="{{ $s['product']['name'] }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                <p class="text-muted small mb-0">
                    <span class="mdi mdi-information-outline"></span>
                    Solid cards are pinned by you; dashed cards are filled automatically (one product per company).
                    Only <strong>active, in-stock</strong> products appear — if a pinned product runs out of stock, its slot is filled automatically until it is back.
                    The photo used is each product's <strong>website image</strong> (set on the product's Edit or Details page).
                    Tip: transparent PNG photos of a single bottle or pack look best.
                </p>
            </div>
        </div>

        <div class="card card-success card-outline mb-4">
            <div class="card-header"><div class="card-title mb-0"><span class="mdi mdi-pin"></span> Choose a product for each slot</div></div>
            <form action="{{ route('website-hero.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 70px">Slot</th>
                                    <th style="min-width: 260px">Showing now</th>
                                    <th style="min-width: 320px">Pin a product</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($slotNames as $slot => $name)
                                    @php
                                        $s        = $shown->get($slot);
                                        $pin      = $pinned->get($slot);
                                        $selected = old('slots.' . $slot, optional($pin)->id);
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge rounded-pill text-bg-warning fs-6">{{ $slot }}</span>
                                            <div class="small text-muted mt-1">{{ $name }}</div>
                                        </td>
                                        <td>
                                            @if($s)
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ $s['product']['image'] }}" class="slot-thumb" alt="">
                                                    <div>
                                                        <div class="fw-semibold">{{ $s['product']['name'] }}</div>
                                                        @if($s['pinned'])
                                                            <span class="badge bg-success">Pinned</span>
                                                        @else
                                                            <span class="badge bg-secondary">Auto</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">Empty</span>
                                            @endif
                                            @if($pin && !$onWebsite->has($pin->id))
                                                <div class="small text-danger mt-1">
                                                    <span class="mdi mdi-alert"></span>
                                                    Pinned "{{ $pin->name }}" is hidden now ({{ $pin->status ? 'out of stock' : 'inactive' }}) — auto photo shown instead.
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <select name="slots[{{ $slot }}]" class="form-select hero-select" data-placeholder="— Auto (filled automatically) —">
                                                <option value="">— Auto (filled automatically) —</option>
                                                @foreach($candidates as $c)
                                                    <option value="{{ $c->id }}"
                                                            data-img="{{ $c->website_image_url }}"
                                                            data-meta="{{ optional($c->company)->name }}{{ $onWebsite->has($c->id) ? '' : ' · out of stock' }}"
                                                            @selected((string) $selected === (string) $c->id)>
                                                        {{ $c->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-success"><span class="mdi mdi-content-save"></span> Save hero images</button>
                    <span class="text-muted small align-self-center">Only products with a photo are listed. Upload photos from Products → Edit.</span>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script>
$(function () {
    function fmt(opt) {
        if (!opt.id) return opt.text;
        const el = opt.element;
        const $o = $('<span class="s2-opt"></span>');
        $('<img alt="">').attr('src', el.dataset.img).appendTo($o);
        const $t = $('<span></span>').appendTo($o);
        $('<span></span>').text(opt.text.trim()).appendTo($t);
        if (el.dataset.meta) $('<br>').appendTo($t), $('<small></small>').text(el.dataset.meta).appendTo($t);
        return $o;
    }
    $('.hero-select').select2({
        width: '100%',
        allowClear: true,
        placeholder: '— Auto (filled automatically) —',
        templateResult: fmt,
        templateSelection: fmt
    });
});
</script>
@endpush
