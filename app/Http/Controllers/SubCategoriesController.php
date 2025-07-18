<?php

namespace App\Http\Controllers;

use App\Models\SubCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
/**
 * @OA\Schema(
 *     schema="SubCategories",
 *     type="object",
 *     title="Sub Category",
 *     required={"id", "name", "category_id"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Shirts"),
 *     @OA\Property(property="category_id", type="integer", example=2)
 * )
 */

class SubCategoriesController extends Controller
{
    /**
     * Get all SubCategories
     *
     * @OA\Get(
     *     path="/api/SubCategories",
     *     summary="Get all SubCategoriess",
     *     tags={"SubCategories"},
     *     @OA\Response(
     *         response=200,
     *         description="List of SubCategories",
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
        return SubCategories::all();
    }

    /**
     * Get SubCategories by ID
     *
     * @OA\Get(
     *     path="/api/SubCategories/{id}",
     *     summary="Get single SubCategories",
     *     tags={"SubCategories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SubCategories details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name_en", type="string", example="Men"),
     *             @OA\Property(property="name_ar", type="string", example="رجالي")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="SubCategories not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="SubCategories not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $SubCategories = SubCategories::find($id);
        if (!$SubCategories) {
            return response()->json(['message' => 'SubCategories not found'], 404);
        }
        return response()->json($SubCategories);
    }

    /**
     * Store new SubCategories
     *
     * @OA\Post(
     *     path="/api/SubCategories",
     *     summary="Create new SubCategories",
     *     tags={"SubCategories"},
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
     *                 @OA\Property(property="logo", type="file"),
     *   @OA\Property(property="category_id", type="integer"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="SubCategories created",
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
            'category_id' => 'required|exists:categories,id',
            'status' => 'nullable|boolean',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('SubCategories', 'public');
        }

        $SubCategories = SubCategories::create($validated);
        return response()->json($SubCategories, 201);
    }

    /**
     * Update SubCategories
     *
     * @OA\Put(
     *     path="/api/SubCategories/{id}",
     *     summary="Update SubCategories",
     *     tags={"SubCategories"},
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
     *                 @OA\Property(property="logo", type="file"),
     *   @OA\Property(property="category_id", type="integer"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SubCategories updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="SubCategories updated")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $SubCategories = SubCategories::find($id);
        if (!$SubCategories) {
            return response()->json(['message' => 'SubCategories not found'], 404);
        }

        $validated = $request->validate([
            'name_en' => 'sometimes|string',
            'name_ar' => 'sometimes|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'status' => 'nullable|boolean',
        ]);

        if ($request->hasFile('logo')) {
            if ($SubCategories->logo) {
                Storage::disk('public')->delete($SubCategories->logo);
            }
            $validated['logo'] = $request->file('logo')->store('SubCategoriess', 'public');
        }

        $SubCategories->update($validated);
        return response()->json(['message' => 'SubCategories updated']);
    }

    /**
     * Delete SubCategories
     *
     * @OA\Delete(
     *     path="/api/SubCategories/{id}",
     *     summary="Delete SubCategories",
     *     tags={"SubCategories"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="SubCategories deleted",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="SubCategories deleted"))
     *     )
     * )
     */
    public function destroy($id)
    {
        $SubCategories = SubCategories::find($id);
        if (!$SubCategories) {
            return response()->json(['message' => 'SubCategories not found'], 404);
        }

        if ($SubCategories->logo) {
            Storage::disk('public')->delete($SubCategories->logo);
        }

        $SubCategories->delete();
        return response()->json(['message' => 'SubCategories deleted']);
    }
     /**
 * Get SubCategories by category ID (paginated)
 *
 * @OA\Get(
 *     path="/api/SubCategories/category/{categoryId}",
 *     summary="Get SubCategories by category ID",
 *     tags={"SubCategories"},
 *     @OA\Parameter(
 *         name="categoryId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="integer", default=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Paginated list of SubCategories by category",
 *         @OA\JsonContent(
 *             @OA\Property(property="pageNumber", type="integer"),
 *             @OA\Property(property="pageSize", type="integer"),
 *             @OA\Property(property="totalPageNumber", type="integer"),
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SubCategories"))
 *         )
 *     )
 * )
 */
public function getByCategory($categoryId)
{
    $SubCategories = SubCategories::where('category_id', $categoryId)->paginate(10);

    return response()->json([
        'pageNumber' => $SubCategories->currentPage(),
        'pageSize' => $SubCategories->perPage(),
        'totalPageNumber' => $SubCategories->lastPage(),
        'data' => $SubCategories->items(),
    ]);
}

}
