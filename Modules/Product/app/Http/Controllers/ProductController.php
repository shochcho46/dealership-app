<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Product;
use Modules\Product\Models\Color;
use Modules\Product\Models\Unit;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['color', 'unit'])->latest()->get();
        return view('product::product.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $colors = Color::active()->get();
        $units = Unit::active()->get();
        return view('product::product.create', compact('colors', 'units'));
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
            'measurement_unit_name' => 'nullable|string|max:255',
            'measurement_unit_number' => 'nullable|string|max:255',
            'package_unit_name' => 'nullable|string|max:255',
            'package_unit_quantity' => 'nullable|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'status' => 'required|boolean',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
            'product_other_images' => 'nullable|array|max:6',
            'product_other_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB each
        ], [
            'name.required' => 'Product name is required.',
            'unit_id.required' => 'Unit is required.',
            'unit_id.exists' => 'Selected unit is invalid.',
            'color_id.exists' => 'Selected color is invalid.',
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
                'measurement_unit_name' => $request->measurement_unit_name,
                'measurement_unit_number' => $request->measurement_unit_number,
                'package_unit_name' => $request->package_unit_name,
                'package_unit_quantity' => $request->package_unit_quantity,
                'unit_id' => $request->unit_id,
                'status' => $request->status
            ]);

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
        $product->load(['color', 'unit']);
        return view('product::product.show', compact('product', 'otherImages'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {

        $colors = Color::active()->get();
        $units = Unit::active()->get();
        return view('product::product.edit', compact('product', 'colors', 'units'));
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
            'measurement_unit_name' => 'nullable|string|max:255',
            'measurement_unit_number' => 'nullable|string|max:255',
            'package_unit_name' => 'nullable|string|max:255',
            'package_unit_quantity' => 'nullable|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'status' => 'required|boolean',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
            'product_other_images' => 'nullable|array|max:6',
            'product_other_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB each
        ], [
            'name.required' => 'Product name is required.',
            'unit_id.required' => 'Unit is required.',
            'unit_id.exists' => 'Selected unit is invalid.',
            'color_id.exists' => 'Selected color is invalid.',
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
                'measurement_unit_name' => $request->measurement_unit_name,
                'measurement_unit_number' => $request->measurement_unit_number,
                'package_unit_name' => $request->package_unit_name,
                'package_unit_quantity' => $request->package_unit_quantity,
                'unit_id' => $request->unit_id,
                'status' => $request->status
            ]);

            // Handle thumbnail image upload
            if ($request->hasFile('product_image')) {
                // Clear existing thumbnail
                $product->clearMediaCollection('product_image');

                // Add new thumbnail
                $product->addMediaFromRequest('product_image')
                    ->toMediaCollection('product_image');
            }

            // Handle other images upload
            if ($request->hasFile('product_other_images')) {
                // Clear existing other images
                $product->clearMediaCollection('product_other_image');

                // Add new other images
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

            $product->delete();
            return redirect()->route('admin.productIndex')->with('success', 'Product deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete product. Please try again.');
        }
    }
}
