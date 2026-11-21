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
        $limit = request()->get('limit', 30);
        $products = Product::with(['color', 'unit', 'company', 'media'])->latest()->paginate($limit);
        // Ids of products the public website shows right now (active + in stock).
        $websiteIds = app(\App\Services\Frontend\CatalogService::class)->products()->keys()->flip();
        return view('product::product.index', compact('products', 'websiteIds'));
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
        $heroHolders = $this->heroHolders();
        return view('product::product.create', compact('colors', 'units', 'companies', 'brands', 'tags', 'heroHolders'));
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
            'mrp' => 'nullable|numeric|min:0|max:99999999',
            'web_price' => 'nullable|numeric|min:0|max:99999999' . ($request->filled('mrp') ? '|lte:mrp' : ''),
            'commission_amount' => 'nullable|numeric|min:0|max:99999999',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'product_other_images' => 'nullable|array|max:6',
            'product_other_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp',
            'brands' => 'nullable|array',
            'brands.*' => 'exists:brands,id',
            'tags' => 'nullable|string',
            'website_image' => ['nullable', 'string', 'regex:/^(media:\d+|new_main|new_other:\d+)$/'],
            'hero_position' => 'nullable|integer|min:1|max:' . Product::HERO_SLOTS,
        ], [
            'web_price.lte' => 'Website price must not be higher than the MRP.',
            'name.required' => 'Product name is required.',
            'unit_id.required' => 'Unit is required.',
            'unit_id.exists' => 'Selected unit is invalid.',
            'color_id.exists' => 'Selected color is invalid.',
            'company_id.exists' => 'Selected company is invalid.',
            'product_image.image' => 'Product image must be an image file.',
            'product_other_images.max' => 'You can upload maximum 6 other images.',
            'product_other_images.*.image' => 'All other images must be image files.',
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
                'mrp' => $request->filled('mrp') ? $request->mrp : null,
                'web_price' => $request->filled('web_price') ? $request->web_price : null,
                'commission_amount' => $request->filled('commission_amount') ? $request->commission_amount : null,
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
            $newMain = null;
            if ($request->hasFile('product_image')) {
                $newMain = $product->addMediaFromRequest('product_image')
                    ->toMediaCollection('product_image');
            }

            // Handle other images upload
            $newOthers = [];
            if ($request->hasFile('product_other_images')) {
                foreach ($request->file('product_other_images') as $file) {
                    $newOthers[] = $product->addMedia($file)
                        ->toMediaCollection('product_other_image');
                }
            }

            $this->applyWebsiteSettings($product, $request, $newMain, $newOthers);

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
     * Choose which of the product's images is shown on the public website.
     */
    public function updateWebsiteImage(Request $request, Product $product)
    {
        $mediaIds = $product->allImages()->pluck('id')->all();

        $request->validate([
            'website_media_id' => ['required', 'integer', \Illuminate\Validation\Rule::in($mediaIds)],
        ], [
            'website_media_id.in' => 'Please choose one of this product\'s images.',
        ]);

        $product->update(['website_media_id' => (int) $request->website_media_id]);

        return back()->with('success', 'Website image updated.');
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
        $heroHolders = $this->heroHolders();
        return view('product::product.edit', compact('product', 'colors', 'units', 'companies', 'brands', 'tags', 'selectedBrands', 'selectedTags', 'heroHolders'));
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
            'mrp' => 'nullable|numeric|min:0|max:99999999',
            'web_price' => 'nullable|numeric|min:0|max:99999999' . ($request->filled('mrp') ? '|lte:mrp' : ''),
            'commission_amount' => 'nullable|numeric|min:0|max:99999999',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'product_other_images' => 'nullable|array|max:6',
            'product_other_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp',
            'brands' => 'nullable|array',
            'brands.*' => 'exists:brands,id',
            'tags' => 'nullable|string',
            'website_image' => ['nullable', 'string', 'regex:/^(media:\d+|new_main|new_other:\d+)$/'],
            'hero_position' => 'nullable|integer|min:1|max:' . Product::HERO_SLOTS,
        ], [
            'web_price.lte' => 'Website price must not be higher than the MRP.',
            'name.required' => 'Product name is required.',
            'unit_id.required' => 'Unit is required.',
            'unit_id.exists' => 'Selected unit is invalid.',
            'color_id.exists' => 'Selected color is invalid.',
            'company_id.exists' => 'Selected company is invalid.',
            'product_image.image' => 'Product image must be an image file.',
            'product_other_images.max' => 'You can upload maximum 6 other images.',
            'product_other_images.*.image' => 'All other images must be image files.',
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
                'mrp' => $request->filled('mrp') ? $request->mrp : null,
                'web_price' => $request->filled('web_price') ? $request->web_price : null,
                'commission_amount' => $request->filled('commission_amount') ? $request->commission_amount : null,
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
            $newMain = null;
            if ($request->hasFile('product_image')) {
                $product->clearMediaCollection('product_image');
                $newMain = $product->addMediaFromRequest('product_image')
                    ->toMediaCollection('product_image');
            }

            // Handle other images upload
            $newOthers = [];
            if ($request->hasFile('product_other_images')) {
                $product->clearMediaCollection('product_other_image');
                foreach ($request->file('product_other_images') as $file) {
                    $newOthers[] = $product->addMedia($file)
                        ->toMediaCollection('product_other_image');
                }
            }

            $this->applyWebsiteSettings($product, $request, $newMain, $newOthers);

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
    /**
     * Save the website image choice and hero slot coming from the create/edit form.
     *
     * website_image values: "media:{id}" (existing image), "new_main" (just-uploaded
     * thumbnail) or "new_other:{n}" (n-th just-uploaded other image). Anything that no
     * longer belongs to the product falls back to null = main image.
     */
    private function applyWebsiteSettings(Product $product, Request $request, $newMain = null, array $newOthers = []): void
    {
        $data = [];

        if ($request->has('website_image')) {
            $choice  = (string) $request->input('website_image');
            $mediaId = null;

            if ($choice === 'new_main') {
                $mediaId = optional($newMain)->id;
            } elseif (preg_match('/^new_other:(\d+)$/', $choice, $m)) {
                $mediaId = optional($newOthers[(int) $m[1]] ?? null)->id;
            } elseif (preg_match('/^media:(\d+)$/', $choice, $m)) {
                $mediaId = (int) $m[1];
            }

            $product->load('media');
            $data['website_media_id'] = $mediaId && $product->allImages()->contains('id', $mediaId) ? $mediaId : null;
        } elseif ($newMain || $newOthers) {
            // Images were replaced without an explicit choice: drop a stale selection.
            $product->load('media');
            if ($product->website_media_id && ! $product->allImages()->contains('id', $product->website_media_id)) {
                $data['website_media_id'] = null;
            }
        }

        if ($request->has('hero_position')) {
            $slot = $request->filled('hero_position') ? (int) $request->input('hero_position') : null;
            if ($slot) {
                // One product per hero slot: free the slot first.
                Product::where('hero_position', $slot)->where('id', '!=', $product->id)->update(['hero_position' => null]);
            }
            $data['hero_position'] = $slot;
        }

        if ($data) {
            $product->update($data);
        }
    }

    /** Products currently pinned to a website hero slot: [slot => ['id', 'name']]. */
    private function heroHolders(): array
    {
        return Product::whereNotNull('hero_position')
            ->get(['id', 'name', 'hero_position'])
            ->mapWithKeys(fn ($p) => [$p->hero_position => ['id' => $p->id, 'name' => $p->name]])
            ->all();
    }

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

