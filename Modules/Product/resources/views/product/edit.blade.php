@extends('layouts.app')

@push('custome-css')
<style>
    .image-preview {
        width: 120px;
        height: 120px;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        object-fit: cover;
        margin: 5px;
    }
    .color-preview {
        width: 30px;

        border: 2px solid #dee2e6;
        display: inline-block;
        vertical-align: middle;
        margin-left: 8px;
    }
    .other-images-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    .existing-images {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
</style>
@endpush

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Product Management</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.productIndex') }}">Products</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
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
                    <h1 class="mt-3">Edit Product: {{ $product->name }}</h1>
                    <div class="text-end">
                        <a href="{{ route('admin.productIndex') }}" class="btn btn-outline-primary">
                            <span class="mdi mdi-format-list-text"></span> View All Products
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
                        <div class="card-title">Product Information</div>
                    </div>
                    <form action="{{ route('admin.productUpdate', $product) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name" placeholder="Enter product name"
                                               value="{{ old('name', $product->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="unit_id" class="form-label">Sale Unit <span class="text-danger">*</span></label>
                                        <select class="form-select @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id" required>
                                            <option value="">Select Unit</option>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>
                                                    {{ $unit->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('unit_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="color_id" class="form-label">Color</label>
                                        <div class="input-group">
                                            <select class="form-select @error('color_id') is-invalid @enderror" id="color_id" name="color_id">
                                                <option value="">Select Color</option>
                                                @foreach($colors as $color)
                                                    <option value="{{ $color->id }}" data-color="{{ $color->code }}" {{ old('color_id', $product->color_id) == $color->id ? 'selected' : '' }}>
                                                        {{ $color->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="ms-2 color-preview" id="selectedColorPreview"
                                                 style="background-color: {{ $product->color->code ?? '#ffffff' }}; display: {{ $product->color ? 'inline-block' : 'none' }};">
                                            </div>
                                        </div>
                                        @error('color_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="">Select Status</option>
                                            <option value="1" {{ old('status', $product->status) == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status', $product->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="measurement_unit_name" class="form-label">Measurement Unit Name</label>
                                        <input type="text" class="form-control @error('measurement_unit_name') is-invalid @enderror"
                                               id="measurement_unit_name" name="measurement_unit_name" placeholder="e.g., cm, inch, meter"
                                               value="{{ old('measurement_unit_name', $product->measurement_unit_name) }}">
                                        @error('measurement_unit_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="measurement_unit_number" class="form-label">Measurement Unit Number</label>
                                        <input type="text" class="form-control @error('measurement_unit_number') is-invalid @enderror"
                                               id="measurement_unit_number" name="measurement_unit_number" placeholder="e.g., 10, 25.5"
                                               value="{{ old('measurement_unit_number', $product->measurement_unit_number) }}">
                                        @error('measurement_unit_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="package_unit_name" class="form-label">Package Unit Name</label>
                                        <input type="text" class="form-control @error('package_unit_name') is-invalid @enderror"
                                               id="package_unit_name" name="package_unit_name" placeholder="e.g., box, pack, carton"
                                               value="{{ old('package_unit_name', $product->package_unit_name) }}">
                                        @error('package_unit_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="package_unit_quantity" class="form-label">Package Unit Quantity</label>
                                        <input type="text" class="form-control @error('package_unit_quantity') is-invalid @enderror"
                                               id="package_unit_quantity" name="package_unit_quantity" placeholder="e.g., 12, 24, 100"
                                               value="{{ old('package_unit_quantity', $product->package_unit_quantity) }}">
                                        @error('package_unit_quantity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="product_image" class="form-label">Product Thumbnail</label>
                                        <input type="file" class="form-control @error('product_image') is-invalid @enderror"
                                               id="product_image" name="product_image" accept="image/*">
                                        @error('product_image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Max file size: 10MB. Leave empty to keep current image.</small>
                                        <div class="mt-2">
                                            <label class="form-label">Current Thumbnail:</label>
                                            <img src="{{ $product->product_image_thumb_url }}" class="image-preview" alt="Current Thumbnail">
                                            <img id="thumbnailPreview" class="image-preview" style="display: none;" alt="New Thumbnail Preview">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="product_other_images" class="form-label">Other Images (Max 6)</label>
                                        <input type="file" class="form-control @error('product_other_images.*') is-invalid @enderror"
                                               id="product_other_images" name="product_other_images[]" accept="image/*" multiple>
                                        @error('product_other_images.*')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Max 6 images, 10MB each. Leave empty to keep current images.</small>

                                        <div class="mt-2">
                                            <label class="form-label">Current Other Images:</label>
                                            <div class="existing-images">
                                                @foreach($product->other_images as $image)
                                                    <img src="{{ $image->getUrl() }}" class="image-preview" alt="Current Image">
                                                @endforeach
                                                @if($product->other_images->count() == 0)
                                                    <p class="text-muted">No other images uploaded</p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="other-images-preview" id="otherImagesPreview"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <span class="mdi mdi-content-save"></span> Update Product
                            </button>
                            <a href="{{ route('admin.productIndex') }}" class="btn btn-secondary">
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
        // Color selection preview
        const colorSelect = document.getElementById('color_id');
        const colorPreview = document.getElementById('selectedColorPreview');

        colorSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const colorCode = selectedOption.getAttribute('data-color');
            if (colorCode) {
                colorPreview.style.backgroundColor = colorCode;
                colorPreview.style.display = 'inline-block';
            } else {
                colorPreview.style.display = 'none';
            }
        });

        // Thumbnail preview
        const thumbnailInput = document.getElementById('product_image');
        const thumbnailPreview = document.getElementById('thumbnailPreview');

        thumbnailInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    thumbnailPreview.src = e.target.result;
                    thumbnailPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                thumbnailPreview.style.display = 'none';
            }
        });

        // Other images preview
        const otherImagesInput = document.getElementById('product_other_images');
        const otherImagesPreview = document.getElementById('otherImagesPreview');

        otherImagesInput.addEventListener('change', function(e) {
            otherImagesPreview.innerHTML = '';
            const files = Array.from(e.target.files);

            if (files.length > 6) {
                alert('You can only select maximum 6 images');
                this.value = '';
                return;
            }

            files.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    img.alt = `New Image ${index + 1}`;
                    otherImagesPreview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    });
</script>
@endpush
