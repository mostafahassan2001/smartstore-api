<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Get all categories with optional pagination
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $pageNumber = $request->input('pageNumber', 1);

        $categories = Category::paginate($pageSize, ['*'], 'page', $pageNumber);

        return response()->json([
            'pageNumber' => $categories->currentPage(),
            'pageSize'   => $categories->perPage(),
            'total'      => $categories->total(),
            'data'       => $categories->items(),
        ]);
    }

    /**
     * Get category by ID
     */
    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category);
    }

    /**
     * Create new category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en'        => 'required|string',
            'name_ar'        => 'required|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'logo'           => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'status'         => 'nullable|boolean',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('categories', 'public');
        }

        $category = Category::create($validated);
        return response()->json($category, 201);
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validated = $request->validate([
            'name_en'        => 'required|string',
            'name_ar'        => 'required|string',
            'description_en' => 'required|string',
            'description_ar' => 'required|string',
            'logo'           => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'status'         => 'nullable|boolean',
        ]);

        if ($request->hasFile('logo')) {
            if ($category->logo) {
                Storage::disk('public')->delete($category->logo);
            }
            $validated['logo'] = $request->file('logo')->store('categories', 'public');
        }

        $category->update($validated);
        return response()->json(['message' => 'Category updated']);
    }

    /**
     * Delete category
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        if ($category->logo) {
            Storage::disk('public')->delete($category->logo);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted']);
    }
}
