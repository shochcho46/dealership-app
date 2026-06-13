<?php

namespace Modules\Product\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Vendor;
use Modules\Product\Http\Resources\VendorResource;

class VendorController extends Controller
{
    /**
     * Search vendors with suggestions
     * Returns vendor with all info, address, and current due balance
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $request->validate([
                'search' => 'nullable|string|max:255',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);

            $search = $request->input('search', '');
            $limit = $request->input('limit', 10);

            $query = Vendor::with(['country', 'vendorAccounts', 'media'])
                ->where('status', 1);

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('shop_name', 'like', '%' . $search . '%')
                      ->orWhere('contact_person', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('mobile', 'like', '%' . $search . '%');
                });
            }

            $vendors = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => VendorResource::collection($vendors),
                'count' => $vendors->count()
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching vendors',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new vendor
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'shop_name' => 'required|string|max:255',
                'contact_person' => 'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:vendors,email',
                'mobile' => 'required|string|max:20|unique:vendors,mobile',
                'country_id' => 'nullable|exists:countries,id',
                'full_address' => 'required|string',
                'lat' => 'nullable|numeric',
                'long' => 'nullable|numeric',
                'status' => 'nullable|boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            $vendor = Vendor::create([
                'shop_name' => $validated['shop_name'],
                'contact_person' => $validated['contact_person'],
                'email' => $validated['email'] ?? null,
                'mobile' => $validated['mobile'],
                'country_id' => $validated['country_id'] ?? 18, // Default to Bangladesh if not provided
                'full_address' => $validated['full_address'],
                'lat' => $validated['lat'] ?? null,
                'long' => $validated['long'] ?? null,
                'status' => $validated['status'] ?? 1,
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                $vendor->addMediaFromRequest('image')->toMediaCollection('vendor_image');
            }

            $vendor->load(['country', 'vendorAccounts', 'media']);

            return response()->json([
                'success' => true,
                'message' => 'Vendor created successfully',
                'data' => new VendorResource($vendor)
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating vendor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get vendor details by ID
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $vendor = Vendor::with(['country', 'vendorAccounts', 'media'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new VendorResource($vendor)
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update vendor details
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $vendor = Vendor::findOrFail($id);

            $validated = $request->validate([
                'shop_name' => 'required|string|max:255',
                'contact_person' => 'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:vendors,email,' . $id,
                'mobile' => 'required|string|max:20|unique:vendors,mobile,' . $id,
                'country_id' => 'nullable|exists:countries,id',
                'full_address' => 'required|string',
                'lat' => 'nullable|numeric',
                'long' => 'nullable|numeric',
                'status' => 'nullable|boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            $vendor->update([
                'shop_name' => $validated['shop_name'],
                'contact_person' => $validated['contact_person'],
                'email' => $validated['email'] ?? null,
                'mobile' => $validated['mobile'],
                'country_id' => $validated['country_id'] ?? 18, // Default to Bangladesh if not provided
                'full_address' => $validated['full_address'],
                'lat' => $validated['lat'] ?? null,
                'long' => $validated['long'] ?? null,
                'status' => $validated['status'] ?? $vendor->status,
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Clear old image
                $vendor->clearMediaCollection('vendor_image');
                // Add new image
                $vendor->addMediaFromRequest('image')->toMediaCollection('vendor_image');
            }

            $vendor->load(['country', 'vendorAccounts', 'media']);

            return response()->json([
                'success' => true,
                'message' => 'Vendor updated successfully',
                'data' => new VendorResource($vendor)
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating vendor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
