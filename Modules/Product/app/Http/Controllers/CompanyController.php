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
        $companies = Company::latest()->get();
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
            'status' => 'required|boolean'
        ], [
            'name.required' => 'Company name is required.',
            'name.unique' => 'This company name already exists.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email already exists.',
            'website.url' => 'Please enter a valid website URL.',
            'status.required' => 'Status is required.'
        ]);

        try {
            Company::create([
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

            return redirect()->route('admin.companyIndex')->with('success', 'Company created successfully!');
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
            'status' => 'required|boolean'
        ], [
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

            return redirect()->route('admin.companyIndex')->with('success', 'Company updated successfully!');
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
            $company->delete();
            return redirect()->route('admin.companyIndex')->with('success', 'Company deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete company. Please try again.');
        }
    }
}
