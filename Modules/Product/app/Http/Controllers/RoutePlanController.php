<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Product\Models\RoutePlan;

class RoutePlanController extends Controller
{
    public function index()
    {
        $limit = request()->get('limit', 30);
        $routePlans = RoutePlan::withCount('vendors')->latest()->paginate($limit);

        return view('product::route-plan.index', compact('routePlans'));
    }

    public function create()
    {
        return view('product::route-plan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:route_plans,name'],
            'description' => ['nullable', 'string'],
        ], [
            'name.required' => 'Route plan name is required.',
            'name.unique' => 'This route plan name already exists.',
        ]);

        try {
            RoutePlan::create($validated);

            return redirect()->route('admin.routePlanIndex')->with('success', 'Route plan created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create route plan. Please try again.')->withInput();
        }
    }

    public function edit(RoutePlan $routePlan)
    {
        return view('product::route-plan.edit', compact('routePlan'));
    }

    public function update(Request $request, RoutePlan $routePlan)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('route_plans', 'name')->ignore($routePlan->id)],
            'description' => ['nullable', 'string'],
        ], [
            'name.required' => 'Route plan name is required.',
            'name.unique' => 'This route plan name already exists.',
        ]);

        try {
            $routePlan->update($validated);

            return redirect()->route('admin.routePlanIndex')->with('success', 'Route plan updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update route plan. Please try again.')->withInput();
        }
    }

    public function destroy(RoutePlan $routePlan)
    {
        if ($routePlan->vendors()->exists()) {
            return back()->with('error', 'This route plan cannot be deleted because it is assigned to one or more vendors.');
        }

        try {
            $routePlan->delete();

            return redirect()->route('admin.routePlanIndex')->with('success', 'Route plan deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete route plan. Please try again.');
        }
    }
}
