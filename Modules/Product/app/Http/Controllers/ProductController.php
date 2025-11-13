<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Product\Models\Product;
use Modules\Product\Models\Color;
use Modules\Product\Models\Unit;
use Modules\Product\Models\Company;
use Modules\Product\Models\Brand;
use Modules\Product\Models\Tag;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['color', 'unit', 'company'])->latest()->get();
        return view('product::product.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $colors = Color::active()->get();
        $units = Unit::active()->get();
        $companies = Company::active()->get();
        $brands = Brand::active()->get();
        $tags = Tag::latest()->get();
        return view('product::product.create', compact('colors', 'units', 'companies', 'brands', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'color_id' => 'nullable|exists:colors,id',
            'company_id' => 'nullable|exists:companies,id',
            'measurement_unit_name' => 'nullable|string|max:255',
            'measurement_unit_number' => 'nullable|string|max:255',
            'package_unit_name' => 'nullable|string|max:255',
            'package_unit_quantity' => 'nullable|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'discount_type' => 'nullable|in:0,1',
            'discount_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'product_other_images' => 'nullable|array|max:6',
            'product_other_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'brands' => 'nullable|array',
            'brands.*' => 'exists:brands,id',
            'tags' => 'nullable|string',
        ], [
            'name.required' => 'Product name is required.',
            'unit_id.required' => 'Unit is required.',
            'unit_id.exists' => 'Selected unit is invalid.',
            'color_id.exists' => 'Selected color is invalid.',
            'company_id.exists' => 'Selected company is invalid.',
            'product_image.image' => 'Product image must be an image file.',
            'product_image.max' => 'Product image must not be larger than 10MB.',
            'product_other_images.max' => 'You can upload maximum 6 other images.',
            'product_other_images.*.image' => 'All other images must be image files.',
            'product_other_images.*.max' => 'Each other image must not be larger than 10MB.',
        ]);

        try {
            $product = Product::create([
                'name' => $request->name,
                'color_id' => $request->color_id,
                'company_id' => $request->company_id,
                'measurement_unit_name' => $request->measurement_unit_name,
                'measurement_unit_number' => $request->measurement_unit_number,
                'package_unit_name' => $request->package_unit_name,
                'package_unit_quantity' => $request->package_unit_quantity,
                'unit_id' => $request->unit_id,
                'discount_type' => $request->discount_type,
                'discount_amount' => $request->discount_amount,
                'description' => $request->description,
                'status' => $request->status
            ]);

            // Handle brands
            if ($request->has('brands') && !empty($request->brands)) {
                $product->brands()->attach($request->brands);
            }

            // Handle tags
            if ($request->has('tags') && !empty($request->tags)) {
                $this->attachTags($product, $request->tags);
            }

            // Handle thumbnail image upload
            if ($request->hasFile('product_image')) {
                $product->addMediaFromRequest('product_image')
                    ->toMediaCollection('product_image');
            }

            // Handle other images upload
            if ($request->hasFile('product_other_images')) {
                foreach ($request->file('product_other_images') as $file) {
                    $product->addMedia($file)
                        ->toMediaCollection('product_other_image');
                }
            }

            return redirect()->route('admin.productIndex')->with('success', 'Product created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create product. Please try again.')->withInput();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show(Product $product)
    {
        $otherImages = $product->getMedia('product_other_image');
        $product->load(['color', 'unit', 'company', 'brands', 'tags']);
        return view('product::product.show', compact('product', 'otherImages'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $colors = Color::active()->get();
        $units = Unit::active()->get();
        $companies = Company::active()->get();
        $brands = Brand::active()->get();
        $tags = Tag::latest()->get();
        $selectedBrands = $product->brands()->pluck('brand_id')->toArray();
        $selectedTags = $product->tags()->pluck('tag_id')->toArray();
        return view('product::product.edit', compact('product', 'colors', 'units', 'companies', 'brands', 'tags', 'selectedBrands', 'selectedTags'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'color_id' => 'nullable|exists:colors,id',
            'company_id' => 'nullable|exists:companies,id',
            'measurement_unit_name' => 'nullable|string|max:255',
            'measurement_unit_number' => 'nullable|string|max:255',
            'package_unit_name' => 'nullable|string|max:255',
            'package_unit_quantity' => 'nullable|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'discount_type' => 'nullable|in:0,1',
            'discount_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'product_other_images' => 'nullable|array|max:6',
            'product_other_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'brands' => 'nullable|array',
            'brands.*' => 'exists:brands,id',
            'tags' => 'nullable|string',
        ], [
            'name.required' => 'Product name is required.',
            'unit_id.required' => 'Unit is required.',
            'unit_id.exists' => 'Selected unit is invalid.',
            'color_id.exists' => 'Selected color is invalid.',
            'company_id.exists' => 'Selected company is invalid.',
            'product_image.image' => 'Product image must be an image file.',
            'product_image.max' => 'Product image must not be larger than 10MB.',
            'product_other_images.max' => 'You can upload maximum 6 other images.',
            'product_other_images.*.image' => 'All other images must be image files.',
            'product_other_images.*.max' => 'Each other image must not be larger than 10MB.',
        ]);

        try {
            $product->update([
                'name' => $request->name,
                'color_id' => $request->color_id,
                'company_id' => $request->company_id,
                'measurement_unit_name' => $request->measurement_unit_name,
                'measurement_unit_number' => $request->measurement_unit_number,
                'package_unit_name' => $request->package_unit_name,
                'package_unit_quantity' => $request->package_unit_quantity,
                'unit_id' => $request->unit_id,
                'discount_type' => $request->discount_type,
                'discount_amount' => $request->discount_amount,
                'description' => $request->description,
                'status' => $request->status
            ]);

            // Handle brands
            if ($request->has('brands')) {
                $product->brands()->sync($request->brands ?? []);
            }

            // Handle tags
            if ($request->has('tags')) {
                $product->tags()->detach();
                if (!empty($request->tags)) {
                    $this->attachTags($product, $request->tags);
                }
            }

            // Handle thumbnail image upload
            if ($request->hasFile('product_image')) {
                $product->clearMediaCollection('product_image');
                $product->addMediaFromRequest('product_image')
                    ->toMediaCollection('product_image');
            }

            // Handle other images upload
            if ($request->hasFile('product_other_images')) {
                $product->clearMediaCollection('product_other_image');
                foreach ($request->file('product_other_images') as $file) {
                    $product->addMedia($file)
                        ->toMediaCollection('product_other_image');
                }
            }

            return redirect()->route('admin.productIndex')->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update product. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            // Clear all media
            $product->clearMediaCollection('product_image');
            $product->clearMediaCollection('product_other_image');

            // Detach relationships
            $product->brands()->detach();
            $product->tags()->detach();

            $product->delete();
            return redirect()->route('admin.productIndex')->with('success', 'Product deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete product. Please try again.');
        }
    }

    /**
     * Attach tags to product - auto-create if not exists
     */
    private function attachTags(Product $product, $tagString)
    {
        if (empty($tagString)) {
            return;
        }

        // Split tags by comma
        $tagNames = array_map('trim', explode(',', $tagString));
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            if (!empty($tagName)) {
                // Create or get tag
                $tag = Tag::firstOrCreate(
                    ['name' => $tagName],
                    ['slug' => Str::slug($tagName)]
                );
                $tagIds[] = $tag->id;
            }
        }

        // Attach tags to product
        if (!empty($tagIds)) {
            $product->tags()->attach($tagIds);
        }
    }
}

