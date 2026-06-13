<?php

namespace Modules\Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Admin\Http\Resources\AdminResource;

class AuthController extends Controller
{
    /**
     * Login API for admin users
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $countryIso = Country::where('id', 18)->first();

            $validated = $request->validate([
                'email_or_phone' => 'required',
                'password' => 'required',
            ]);

            // Determine if login is via email or phone
            if (filter_var($request->email_or_phone, FILTER_VALIDATE_EMAIL)) {
                $credential = [
                    'email' => $request->email_or_phone,
                    'password' => $request->password
                ];
            } else {
                $phoneNumber = validationMobileNumber($request->email_or_phone, $countryIso->iso);
                $credential = [
                    'phone' => $phoneNumber,
                    'password' => $request->password
                ];
            }

            // Attempt authentication
            if (Auth::guard('admin')->attempt($credential)) {
                $admin = Auth::guard('admin')->user();

                // Check if account is active
                if ($admin->status == 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This account is blacklisted'
                    ], 403);
                }

                // Create API token
                $token = $admin->createToken('AdminAPIToken')->accessToken;

                // Load relationships for resource
                $admin->load('roles.permissions', 'media');

                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'admin' => new AdminResource($admin),
                        'token' => $token,
                        'token_type' => 'Bearer'
                    ]
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout API for admin users
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // Revoke current access token
            $request->user()->token()->revoke();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated admin user profile
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        try {
            $admin = $request->user();
            $admin->load('roles.permissions', 'media');

            return response()->json([
                'success' => true,
                'data' => new AdminResource($admin)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
