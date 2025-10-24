@extends('layouts.app')

@push('custome-css')
<style>
    .color-preview {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        display: inline-block;
        vertical-align: middle;
        margin-left: 10px;
    }
</style>
@endpush

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Color Management</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.colorIndex') }}">Colors</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="mt-3">Create New Color</h1>
                    <div class="text-end">
                        <a href="{{ route('admin.colorIndex') }}" class="btn btn-outline-primary">
                            <span class="mdi mdi-format-list-text"></span> View All Colors
                        </a>
                    </div>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card card-primary card-outline mb-4">
                    <div class="card-header">
                        <div class="card-title">Color Information</div>
                    </div>
                    <form action="{{ route('admin.colorStore') }}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Color Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name" placeholder="Enter color name"
                                               value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="code" class="form-label">Color Code <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color @error('code') is-invalid @enderror"
                                                   id="colorPicker" value="{{ old('code', '#000000') }}" style="width: 60px;">
                                            <input type="text" class="form-control @error('code') is-invalid @enderror"
                                                   id="code" name="code" placeholder="#000000"
                                                   value="{{ old('code', '#000000') }}" pattern="^#[a-fA-F0-9]{6}$" required>
                                            <div class="color-preview" id="colorPreview" style="background-color: {{ old('code', '#000000') }}"></div>
                                        </div>
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Click the color box to pick a color or enter hex code manually</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="">Select Status</option>
                                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <span class="mdi mdi-content-save"></span> Save Color
                            </button>
                            <a href="{{ route('admin.colorIndex') }}" class="btn btn-secondary">
                                <span class="mdi mdi-cancel"></span> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const colorPicker = document.getElementById('colorPicker');
        const colorInput = document.getElementById('code');
        const colorPreview = document.getElementById('colorPreview');

        // Update text input and preview when color picker changes
        colorPicker.addEventListener('input', function() {
            const color = this.value;
            colorInput.value = color;
            colorPreview.style.backgroundColor = color;
        });

        // Update color picker and preview when text input changes
        colorInput.addEventListener('input', function() {
            const color = this.value;
            if (/^#[a-fA-F0-9]{6}$/.test(color)) {
                colorPicker.value = color;
                colorPreview.style.backgroundColor = color;
            }
        });

        // Ensure proper format on blur
        colorInput.addEventListener('blur', function() {
            let color = this.value;
            if (color && !color.startsWith('#')) {
                color = '#' + color;
            }
            if (color.length === 7 && /^#[a-fA-F0-9]{6}$/.test(color)) {
                this.value = color;
                colorPicker.value = color;
                colorPreview.style.backgroundColor = color;
            }
        });
    });
</script>
@endpush
