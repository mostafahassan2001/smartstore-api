<?php

namespace App\Http\Controllers;

use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="SubCategory",
 *     type="object",
 *     title="Sub Category",
 *     required={"id", "name_en", "name_ar", "category_id"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name_en", type="string", example="Shirts"),
 *     @OA\Property(property="name_ar", type="string", example="قمصان"),
 *     @OA\Property(property="description_en", type="string", example="Subcategory for men's shirts"),
 *     @OA\Property(property="description_ar", type="string", example="قسم فرعي للقمصان الرجالية"),
 *     @OA\Property(property="category_id", type="integer", example=2),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(property="logo", type="string", example="SubCategory/logo.jpg")
 * )
 */
class SubCategoriesController extends Controller
{
   /**
 * @OA\Get(
 *     path="/api/subcategories",
 *     summary="Get paginated list of SubCategory",
 *     tags={"SubCategory"},
 *     @OA\Parameter(
 *         name="pageNumber",
 *         in="query",
 *         description="Page number",
 *         required=false,
 *         @OA\Schema(type="integer", default=1)
 *     ),
 *     @OA\Parameter(
 *         name="pageSize",
 *         in="query",
 *         description="Number of items per page",
 *         required=false,
 *         @OA\Schema(type="integer", default=10)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful response with paginated SubCategory",
 *         @OA\JsonContent(
 *             @OA\Property(property="pageNumber", type="integer"),
 *             @OA\Property(property="pageSize", type="integer"),
 *             @OA\Property(property="totalPageNumber", type="integer"),
 *             @OA\Property(property="totalItems", type="integer"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/SubCategory")
 *             )
 *         )
 *     )
 * )
 */
public function index(Request $request)
{
    try {
        $pageSize = $request->input('pageSize', 10);
        $pageNumber = $request->input('pageNumber', 1);

        $subs = SubCategory::paginate($pageSize, ['*'], 'page', $pageNumber);

        return response()->json([
            'pageNumber' => $subs->currentPage(),
            'pageSize' => $subs->perPage(),
            'totalPageNumber' => $subs->lastPage(),
            'totalItems' => $subs->total(),
            'data' => $subs->items(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTrace(),
        ], 500);
    }
}


    /**
     * @OA\Get(
     *     path="/api/subcategories/{id}",
     *     summary="Get single SubCategory by ID",
     *     tags={"SubCategory"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="SubCategory details", @OA\JsonContent(ref="#/components/schemas/SubCategory")),
     *     @OA\Response(response=404, description="SubCategory not found")
     * )
     */
    public function show($id)
    {
        $sub = SubCategory::find($id);
        if (!$sub) {
            return response()->json(['message' => 'SubCategory not found'], 404);
        }
        return response()->json($sub);
    }

    /**
     * @OA\Post(
     *     path="/api/subcategories",
     *     summary="Create new SubCategory",
     *     tags={"SubCategory"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name_en", "name_ar", "category_id", "description_en", "description_ar"},
     *                 @OA\Property(property="name_en", type="string"),
     *                 @OA\Property(property="name_ar", type="string"),
     *                 @OA\Property(property="description_en", type="string"),
     *                 @OA\Property(property="description_ar", type="string"),
     *                 @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="status", type="boolean"),
     *                 @OA\Property(property="logo", type="file")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="SubCategory created", @OA\JsonContent(ref="#/components/schemas/SubCategory"))
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string',
            'name_ar' => 'required|string',
            'description_en' => 'required|string',
            'description_ar' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|boolean',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('SubCategory', 'public');
        }

        $sub = SubCategory::create($validated);
        return response()->json($sub, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/subcategories/{id}",
     *     summary="Update SubCategory",
     *     tags={"SubCategory"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name_en", "name_ar", "category_id", "description_en", "description_ar"},
     *                 @OA\Property(property="name_en", type="string"),
     *                 @OA\Property(property="name_ar", type="string"),
     *                 @OA\Property(property="description_en", type="string"),
     *                 @OA\Property(property="description_ar", type="string"),
     *                 @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="status", type="boolean"),
     *                 @OA\Property(property="logo", type="file")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="SubCategory updated", @OA\JsonContent(@OA\Property(property="message", type="string", example="SubCategory updated")))
     * )
     */
    public function update(Request $request, $id)
    {
        $sub = SubCategory::find($id);
        if (!$sub) {
            return response()->json(['message' => 'SubCategory not found'], 404);
        }

        $validated = $request->validate([
            'name_en' => 'required|string',
            'name_ar' => 'required|string',
            'description_en' => 'required|string',
            'description_ar' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|boolean',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($sub->logo) {
                Storage::disk('public')->delete($sub->logo);
            }
            $validated['logo'] = $request->file('logo')->store('SubCategory', 'public');
        }

        $sub->update($validated);
        return response()->json(['message' => 'SubCategory updated']);
    }

    /**
     * @OA\Delete(
     *     path="/api/subcategories/{id}",
     *     summary="Delete SubCategory",
     *     tags={"SubCategory"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="SubCategory deleted", @OA\JsonContent(@OA\Property(property="message", type="string", example="SubCategory deleted")))
     * )
     */
    public function destroy($id)
    {
        $sub = SubCategory::find($id);
        if (!$sub) {
            return response()->json(['message' => 'SubCategory not found'], 404);
        }

        if ($sub->logo) {
            Storage::disk('public')->delete($sub->logo);
        }

        $sub->delete();
        return response()->json(['message' => 'SubCategory deleted']);
    }

    /**
     * @OA\Get(
     *     path="/api/subcategories/category/{categoryId}",
     *     summary="Get SubCategory by category ID",
     *     tags={"SubCategory"},
     *     @OA\Parameter(name="categoryId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Response(response=200, description="Paginated SubCategory by category",
     *         @OA\JsonContent(
     *             @OA\Property(property="pageNumber", type="integer"),
     *             @OA\Property(property="pageSize", type="integer"),
     *             @OA\Property(property="totalPageNumber", type="integer"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SubCategory"))
     *         )
     *     )
     * )
     */
   public function getByCategory(Request $request, $categoryId)
{
    $pageSize = $request->input('pageSize', 10);
    $pageNumber = $request->input('pageNumber', 1);

    $subs = SubCategory::where('category_id', $categoryId)
                ->paginate($pageSize, ['*'], 'page', $pageNumber);

    return response()->json([
        'pageNumber' => $subs->currentPage(),
        'pageSize' => $subs->perPage(),
        'totalPageNumber' => $subs->lastPage(),
        'data' => $subs->items(),
    ]);
}

}
