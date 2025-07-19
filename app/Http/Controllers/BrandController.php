<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    /**
     * Display a paginated list of brands.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $pageNumber = $request->input('pageNumber', 1);

        $brands = Brand::paginate($pageSize, ['*'], 'page', $pageNumber);

        return response()->json([
            'pageNumber' => $brands->currentPage(),
            'pageSize'   => $brands->perPage(),
            'total'      => $brands->total(),
            'data'       => $brands->items(),
        ]);
    }

    /**
     * Display the specified brand.
     */
    public function show($id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        return response()->json($brand);
    }

    /**
     * Store a newly created brand in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en'         => 'required|string',
            'name_ar'         => 'required|string',
            'description_en'  => 'required|string',
            'description_ar'  => 'required|string',
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'status'          => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand = Brand::create($validated);
        return response()->json($brand, 201);
    }

    /**
     * Update the specified brand.
     */
    public function update(Request $request, $id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        $validated = $request->validate([
            'name_en'         => 'required|string',
            'name_ar'         => 'required|string',
            'description_en'  => 'required|string',
            'description_ar'  => 'required|string',
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'status'          => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand->update($validated);
        return response()->json($brand);
    }

    /**
     * Remove the specified brand from storage.
     */
    public function destroy($id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();
        return response()->json(['message' => 'Brand deleted']);
    }
}
