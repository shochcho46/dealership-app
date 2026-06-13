<?php

namespace Modules\Product\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Product;
use Modules\Product\Http\Resources\ProductResource;

class ProductController extends Controller
{
    /**
     * Search products with suggestions
     * Returns product with image, name, quantity available, and sold price
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

            $query = Product::withAvailableStocks()
                ->with(['color', 'company', 'unit', 'media'])
                ->where('status', 1);

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhereHas('company', function ($companyQuery) use ($search) {
                          $companyQuery->where('name', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('color', function ($colorQuery) use ($search) {
                          $colorQuery->where('name', 'like', '%' . $search . '%');
                      });
                });
            }

            $products = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($products),
                'count' => $products->count()
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
                'message' => 'An error occurred while searching products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product details by ID
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $product = Product::withAvailableStocks()
                ->with(['color', 'company', 'unit', 'media'])
                ->where('status', 1)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new ProductResource($product)
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
