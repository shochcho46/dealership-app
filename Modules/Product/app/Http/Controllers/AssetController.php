<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Asset;

class AssetController extends Controller
{
    public function index()
    {
        $limit = request()->get('limit', 30);
        $assets = Asset::latest()->paginate($limit);
        return view('product::asset.index', compact('assets'));
    }

    public function create()
    {
        return view('product::asset.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:1,2',
            'status' => 'required|boolean'
        ]);

        try {
            Asset::create($request->all());
            return redirect()->route('admin.assetIndex')->with('success', 'Asset created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create asset.')->withInput();
        }
    }

    public function edit(Asset $asset)
    {
        return view('product::asset.edit', compact('asset'));
    }

    public function update(Request $request, Asset $asset)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:1,2',
            'status' => 'required|boolean'
        ]);

        try {
            $asset->update($request->all());
            return redirect()->route('admin.assetIndex')->with('success', 'Asset updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update asset.')->withInput();
        }
    }

    public function destroy(Asset $asset)
    {
        try {
            $asset->delete();
            return redirect()->route('admin.assetIndex')->with('success', 'Asset deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete asset.');
        }
    }
}
