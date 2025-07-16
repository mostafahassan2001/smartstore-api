<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Get all categories
     *
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Get all categories",
     *     tags={"Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name_en", type="string", example="Men"),
     *             @OA\Property(property="name_ar", type="string", example="رجالي")
     *         ))
     *     )
     * )
     */
    public function index()
    {
        return Category::all();
    }

    /**
     * Get category by ID
     *
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     summary="Get single category",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name_en", type="string", example="Men"),
     *             @OA\Property(property="name_ar", type="string", example="رجالي")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     )
     * )
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
     * Store new category
     *
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Create new category",
     *     tags={"Categories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name_en", "name_ar"},
     *                 @OA\Property(property="name_en", type="string"),
     *                 @OA\Property(property="name_ar", type="string"),
     *                 @OA\Property(property="description_en", type="string"),
     *                 @OA\Property(property="description_ar", type="string"),
     *                 @OA\Property(property="status", type="boolean"),
     *                 @OA\Property(property="logo", type="file")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name_en", type="string"),
     *             @OA\Property(property="logo", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string',
            'name_ar' => 'required|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'status' => 'nullable|boolean',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('categories', 'public');
        }

        $category = Category::create($validated);
        return response()->json($category, 201);
    }

    /**
     * Update category
     *
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     summary="Update category",
     *     tags={"Categories"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name_en", type="string"),
     *                 @OA\Property(property="name_ar", type="string"),
     *                 @OA\Property(property="description_en", type="string"),
     *                 @OA\Property(property="description_ar", type="string"),
     *                 @OA\Property(property="status", type="boolean"),
     *                 @OA\Property(property="logo", type="file")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category updated")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validated = $request->validate([
            'name_en' => 'sometimes|string',
            'name_ar' => 'sometimes|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'status' => 'nullable|boolean',
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
     *
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     summary="Delete category",
     *     tags={"Categories"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Category deleted"))
     *     )
     * )
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
