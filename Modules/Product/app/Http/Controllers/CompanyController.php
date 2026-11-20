<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Company;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $limit = request()->get('limit', 30);
        $companies = Company::with('media')->latest()->paginate($limit);
        return view('product::company.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('product::company.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'name' => 'required|string|max:255|unique:companies,name',
            'email' => 'nullable|email|unique:companies,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url',
            'status' => 'required|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg', // no size limit
            'remove_logo' => 'nullable|boolean',
        ], [
            'logo.image' => 'The logo must be an image file.',
            'logo.mimes' => 'The logo must be a JPG, PNG, WEBP, GIF or SVG file.',
            'name.required' => 'Company name is required.',
            'name.unique' => 'This company name already exists.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email already exists.',
            'website.url' => 'Please enter a valid website URL.',
            'status.required' => 'Status is required.'
        ]);

        try {
            $company = Company::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'website' => $request->website,
                'status' => $request->status
            ]);

            $logoError = $this->saveLogo($request, $company);

            return redirect()->route('admin.companyIndex')
                ->with('success', 'Company created successfully!')
                ->with('error', $logoError);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create company. Please try again.')->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        return view('product::company.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        // Validation
        $request->validate([
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
            'email' => 'nullable|email|unique:companies,email,' . $company->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url',
            'status' => 'required|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg', // no size limit
            'remove_logo' => 'nullable|boolean',
        ], [
            'logo.image' => 'The logo must be an image file.',
            'logo.mimes' => 'The logo must be a JPG, PNG, WEBP, GIF or SVG file.',
            'name.required' => 'Company name is required.',
            'name.unique' => 'This company name already exists.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email already exists.',
            'website.url' => 'Please enter a valid website URL.',
            'status.required' => 'Status is required.'
        ]);

        try {
            $company->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'website' => $request->website,
                'status' => $request->status
            ]);

            $logoError = $this->saveLogo($request, $company);

            return redirect()->route('admin.companyIndex')
                ->with('success', 'Company updated successfully!')
                ->with('error', $logoError);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update company. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        try {
            $company->clearMediaCollection('logo');
            $company->delete();
            return redirect()->route('admin.companyIndex')->with('success', 'Company deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete company. Please try again.');
        }
    }

    /**
     * Upload a new logo (replaces the old one) or remove it when "Remove current logo" is ticked.
     */
    private function saveLogo(Request $request, Company $company): ?string
    {
        try {
            if ($request->hasFile('logo')) {
                $company->addMediaFromRequest('logo')->toMediaCollection('logo');
            } elseif ($request->boolean('remove_logo')) {
                $company->clearMediaCollection('logo');
            }
        } catch (\Throwable $e) {
            report($e);
            // The company itself is saved; only the logo failed.
            return 'Company saved, but the logo could not be uploaded: ' . $e->getMessage();
        }

        return null;
    }
}
