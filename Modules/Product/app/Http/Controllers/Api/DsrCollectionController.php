<?php

namespace Modules\Product\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Product\Models\DsrCollection;
use Modules\Product\Models\Vendor;
use Modules\Product\Models\PaymentMethod;
use Modules\Product\Http\Resources\DsrCollectionResource;

class DsrCollectionController extends Controller
{
    /**
     * List DSR collections with filters
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $request->validate([
                'vendor_id' => 'nullable|exists:vendors,id',
                'payment_method_id' => 'nullable|exists:payment_methods,id',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $perPage = $request->input('per_page', 15);
            $query = DsrCollection::with(['vendor', 'paymentMethod', 'createdBy', 'depositeBy']);

            // Apply filters
            if ($request->filled('vendor_id')) {
                $query->where('vendor_id', $request->vendor_id);
            }

            if ($request->filled('payment_method_id')) {
                $query->where('payment_method_id', $request->payment_method_id);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('collection_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('collection_date', '<=', $request->date_to);
            }

            $collections = $query->orderBy('id', 'desc')->paginate($perPage);

            // Calculate filtered total
            $filteredQuery = DsrCollection::query();
            if ($request->filled('vendor_id')) {
                $filteredQuery->where('vendor_id', $request->vendor_id);
            }
            if ($request->filled('payment_method_id')) {
                $filteredQuery->where('payment_method_id', $request->payment_method_id);
            }
            if ($request->filled('date_from')) {
                $filteredQuery->whereDate('collection_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $filteredQuery->whereDate('collection_date', '<=', $request->date_to);
            }
            $filteredTotal = $filteredQuery->sum('amount');

            return response()->json([
                'success' => true,
                'data' => DsrCollectionResource::collection($collections),
                'meta' => [
                    'current_page' => $collections->currentPage(),
                    'last_page' => $collections->lastPage(),
                    'per_page' => $collections->perPage(),
                    'total' => $collections->total(),
                    'filtered_total_amount' => number_format($filteredTotal, 2, '.', ''),
                    'total_all_time' => number_format(DsrCollection::sum('amount'), 2, '.', '')
                ]
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
                'message' => 'An error occurred while fetching collections',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new DSR collection
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'vendor_id' => 'required|exists:vendors,id',
                'payment_method_id' => 'required|exists:payment_methods,id',
                'amount' => 'required|numeric|min:0.01',
                'collection_date' => 'required|date',
                'note' => 'nullable|string|max:1000',
            ]);

            // Use the currently authenticated admin as the depositor
            $depositeBy = $request->user()->id;

            $collection = DsrCollection::create([
                'vendor_id' => $validated['vendor_id'],
                'payment_method_id' => $validated['payment_method_id'],
                'amount' => $validated['amount'],
                'collection_date' => $validated['collection_date'],
                'note' => $validated['note'] ?? null,
                'deposite_by' => $depositeBy,
            ]);

            // Send SMS notification if configured
            $vendor = Vendor::find($validated['vendor_id']);
            if ($vendor && $vendor->mobile && config('app.collection_sms') == 1) {
                $message = date('d-m-Y')
                    . " জমা: ৳" . number_format($validated['amount'], 2)
                    . "\nমোট বকেয়া: ৳" . number_format($vendor->due_balance, 2)
                    . "\n\n- এস এস এন্টারপ্রাইজ";
                
                try {
                    Http::asForm()->post('https://api.bdbulksms.net/api.php?json', [
                        'to' => $vendor->mobile,
                        'message' => $message,
                        'token' => 'c3253885b10f98c971b719b5372a4b34'
                    ]);
                } catch (\Exception $e) {
                    Log::error('SMS sending failed: ' . $e->getMessage());
                }
            }

            $collection->load(['vendor', 'paymentMethod', 'createdBy', 'depositeBy']);

            return response()->json([
                'success' => true,
                'message' => 'Collection recorded successfully',
                'data' => new DsrCollectionResource($collection)
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
                'message' => 'An error occurred while creating collection',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get DSR collection details by ID
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $collection = DsrCollection::with(['vendor', 'paymentMethod', 'createdBy', 'depositeBy'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new DsrCollectionResource($collection)
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Collection not found'
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
     * Delete a DSR collection
     * Only SuperAdmin or admin roles can delete
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $collection = DsrCollection::findOrFail($id);

            // Check if user has permission to delete (SuperAdmin or admin)
            $user = $request->user();
            if (!$user->hasAnyRole(['SuperAdmin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this collection'
                ], 403);
            }

            $collection->delete();

            return response()->json([
                'success' => true,
                'message' => 'Collection deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Collection not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting collection',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search vendors for collection form
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchVendors(Request $request)
    {
        try {
            $request->validate([
                'search' => 'nullable|string|max:255',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);

            $search = $request->input('search', '');
            $limit = $request->input('limit', 10);

            $query = Vendor::query();

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('shop_name', 'LIKE', "%{$search}%")
                      ->orWhere('mobile', 'LIKE', "%{$search}%");
                });
            }

            $vendors = $query->limit($limit)->get(['id', 'shop_name', 'mobile', 'full_address', 'contact_person', 'due_balance']);

            return response()->json([
                'success' => true,
                'data' => $vendors,
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
}
