@extends('layouts.app')

@push('custome-css')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Tagify CSS -->
<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css">
<!-- CKEditor CSS -->
<link href="https://cdn.jsdelivr.net/npm/ckeditor5@latest/dist/ckeditor5.css" rel="stylesheet" type="text/css">

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

    /* Tagify Styling */
    .tagify {
        background-color: white;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.375rem;
        min-height: 38px;
    }

    .tagify__tag {
        background-color: #0d6efd;
        color: white;
        padding: 0.375rem 0.75rem;
        border-radius: 0.25rem;
        margin: 0.25rem;
    }

    .tagify__tag__removeBtn {
        opacity: 1;
        color: white;
    }

    .select2-container--default .select2-selection--multiple {
        border-radius: 0.375rem;
        min-height: 38px;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    /* CKEditor Styling */
    .ck-editor__editable {
        min-height: 200px;
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
                    <h1 class="mt-3">Create New Product</h1>
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
                    <form action="{{ route('admin.productStore') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name" placeholder="Enter product name"
                                               value="{{ old('name') }}" required>
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
                                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
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
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="color_id" class="form-label">Color</label>
                                        <div class="input-group">
                                            <select class="form-select @error('color_id') is-invalid @enderror" id="color_id" name="color_id">
                                                <option value="">Select Color</option>
                                                @foreach($colors as $color)
                                                    <option value="{{ $color->id }}" data-color="{{ $color->code }}" {{ old('color_id') == $color->id ? 'selected' : '' }}>
                                                        {{ $color->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="ms-2 color-preview" id="selectedColorPreview"></div>
                                        </div>
                                        @error('color_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="company_id" class="form-label">Company</label>
                                        <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id">
                                            <option value="">Select Company</option>
                                            @foreach($companies as $company)
                                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                    {{ $company->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('company_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
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

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="brands" class="form-label">Brands (Select Multiple)</label>
                                        <select class="form-select brands-select @error('brands') is-invalid @enderror" id="brands" name="brands[]" multiple style="width: 100%;">
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}" {{ old('brands') && in_array($brand->id, old('brands', [])) ? 'selected' : '' }}>
                                                    {{ $brand->name }} ({{ $brand->company->name ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('brands')
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
                                               value="{{ old('measurement_unit_name') }}">
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
                                               value="{{ old('measurement_unit_number') }}">
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
                                               value="{{ old('package_unit_name') }}">
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
                                               value="{{ old('package_unit_quantity') }}">
                                        @error('package_unit_quantity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="discount_type" class="form-label">Discount Type</label>
                                        <select class="form-select @error('discount_type') is-invalid @enderror" id="discount_type" name="discount_type">
                                            <option value="">Select Discount Type</option>
                                            <option value="0" {{ old('discount_type') == '0' ? 'selected' : '' }}>Fixed</option>
                                            <option value="1" {{ old('discount_type') == '1' ? 'selected' : '' }}>Percent (%)</option>
                                        </select>
                                        @error('discount_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="discount_amount" class="form-label">Discount Amount</label>
                                        <input type="number" class="form-control @error('discount_amount') is-invalid @enderror"
                                               id="discount_amount" name="discount_amount" placeholder="0.00" step="0.01"
                                               value="{{ old('discount_amount') }}">
                                        @error('discount_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="tags-input" class="form-label">Tags</label>
                                        <input type="text" id="tags-input" name="tags" class="form-control @error('tags') is-invalid @enderror"
                                               placeholder="Type tags and press space"
                                               value="{{ old('tags') }}" />
                                        <small class="form-text text-muted">Press space to add tags, will auto-create if new</small>
                                        @error('tags')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                        @error('description')
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
                                        <small class="form-text text-muted">Max file size: 10MB. This will be the main product image.</small>
                                        <div class="mt-2">
                                            <img id="thumbnailPreview" class="image-preview" style="display: none;" alt="Thumbnail Preview">
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
                                        <small class="form-text text-muted">Max 6 images, 10MB each. Additional product images.</small>
                                        <div class="other-images-preview" id="otherImagesPreview"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <span class="mdi mdi-content-save"></span> Save Product
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
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Tagify JS -->
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<!-- CKEditor JS -->
<script src="https://cdn.jsdelivr.net/npm/ckeditor5@latest/dist/ckeditor5.umd.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ===== Color Selection Preview =====
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

        // ===== Select2 for Brands =====
        $('.brands-select').select2({
            placeholder: 'Select brands...',
            allowClear: true,
            width: '100%'
        });

        // ===== Tagify for Tags =====
        const tagsInput = document.getElementById('tags-input');
        const tagify = new Tagify(tagsInput, {
            delimiter: ' ,',  // Space or comma as delimiter
            maxTags: 20,
            whitelist: [],
            dropdown: {
                maxItems: 20,
                classname: "tags-look",
                enabled: 0,
                closeOnSelect: false
            },
            templates: {
                tag: function(tagData) {
                    return `<tag title='${tagData.value}' contenteditable='false' spellcheck='false' class='tagify__tag' data-value='${tagData.value}'>
                        <x title='remove tag' class='tagify__tag__removeBtn'></x>
                        <span class='tagify__tag-text'>${tagData.value}</span>
                    </tag>`;
                },
                dropdownItem: function(item) {
                    return `<div class='tagify__dropdown__item' data-value='${item.value}'>${item.value}</div>`;
                }
            }
        });

        // Format tags on form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            // Tagify automatically updates the input value
            const tagsValue = tagify.value.map(tag => tag.value).join(',');
            if (tagsValue) {
                tagsInput.value = tagsValue;
            }
        });

        // ===== CKEditor 5 for Description =====
        const { ClassicEditor, Essentials, Paragraph, Bold, Italic, Underline, Strikethrough, Link, List, BlockQuote, Heading, HtmlComment } = window.CKEDITOR;

        ClassicEditor
            .create(document.getElementById('description'), {
                plugins: [
                    Essentials,
                    Paragraph,
                    Bold,
                    Italic,
                    Underline,
                    Strikethrough,
                    Link,
                    List,
                    BlockQuote,
                    Heading,
                    HtmlComment
                ],
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'underline', 'strikethrough', '|',
                    'link', 'blockQuote', 'numberedList', 'bulletedList', '|',
                    'undo', 'redo'
                ],
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                        { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                    ]
                }
            })
            .then(editor => {
                window.editorInstance = editor;
            })
            .catch(error => {
                console.error('CKEditor initialization error:', error);
            });

        // ===== Thumbnail Preview =====
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

        // ===== Other Images Preview =====
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
                    img.alt = `Other Image ${index + 1}`;
                    otherImagesPreview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    });
</script>
@endpush
