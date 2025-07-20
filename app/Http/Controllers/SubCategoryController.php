<?php

namespace App\Http\Controllers;

use App\Models\SubCategory;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="SubCategories",
 *     description="Operations related to subcategories"
 * )
 */
class SubCategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/subcategories",
     *     summary="Get all subcategories",
     *     tags={"SubCategories"},
     *     @OA\Response(
     *         response=200,
     *         description="List of subcategories"
     *     )
     * )
     */
    public function index()
    {
        return response()->json(SubCategory::all());
    }

    /**
     * @OA\Get(
     *     path="/api/subcategories/{id}",
     *     summary="Get subcategory by ID",
     *     tags={"SubCategories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Subcategory found"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show($id)
    {
        $subcategory = SubCategory::find($id);
        if (!$subcategory) {
            return response()->json(['message' => 'Subcategory not found'], 404);
        }
        return response()->json($subcategory);
    }

    /**
     * @OA\Post(
     *     path="/api/subcategories",
     *     summary="Create a new subcategory",
     *     tags={"SubCategories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name_en", "name_ar", "description_en", "description_ar", "category_id", "status"},
     *             @OA\Property(property="name_en", type="string"),
     *             @OA\Property(property="name_ar", type="string"),
     *             @OA\Property(property="description_en", type="string"),
     *             @OA\Property(property="description_ar", type="string"),
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="logo", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Subcategory created")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string',
            'name_ar' => 'required|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|boolean',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('subcategories', 'public');
        }

        $subcategory = SubCategory::create($validated);
        return response()->json($subcategory, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/subcategories/{id}",
     *     summary="Update subcategory",
     *     tags={"SubCategories"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name_en", type="string"),
     *             @OA\Property(property="name_ar", type="string"),
     *             @OA\Property(property="description_en", type="string"),
     *             @OA\Property(property="description_ar", type="string"),
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="logo", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Subcategory updated")
     * )
     */
    public function update(Request $request, $id)
    {
        $subcategory = SubCategory::find($id);
        if (!$subcategory) {
            return response()->json(['message' => 'Subcategory not found'], 404);
        }

        $validated = $request->validate([
            'name_en' => 'required|string',
            'name_ar' => 'required|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|boolean',
        ]);

        if ($request->hasFile('logo')) {
            // optionally delete old logo
            $validated['logo'] = $request->file('logo')->store('subcategories', 'public');
        }

        $subcategory->update($validated);
        return response()->json($subcategory);
    }

    /**
     * @OA\Delete(
     *     path="/api/subcategories/{id}",
     *     summary="Delete subcategory",
     *     tags={"SubCategories"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Subcategory deleted")
     * )
     */
    public function destroy($id)
    {
        $subcategory = SubCategory::find($id);
        if (!$subcategory) {
            return response()->json(['message' => 'Subcategory not found'], 404);
        }

        $subcategory->delete();
        return response()->json(['message' => 'Subcategory deleted']);
    }

    /**
     * @OA\Get(
     *     path="/api/category/{categoryId}/subcategories",
     *     summary="Get subcategories by category ID",
     *     tags={"SubCategories"},
     *     @OA\Parameter(name="categoryId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of subcategories")
     * )
     */
    public function getByCategory($categoryId)
    {
        $subs = SubCategory::where('category_id', $categoryId)->get();
        return response()->json($subs);
    }
}
