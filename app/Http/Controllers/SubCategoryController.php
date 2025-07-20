<?php

namespace App\Http\Controllers;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


/**
 * @OA\Schema(
 *     schema="SubCategory",
 *     type="object",
 *     title="SubCategory",
 *     required={"id", "name_en", "name_ar"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name_en", type="string", example="Nike"),
 *     @OA\Property(property="name_ar", type="string", example="نايك"),
 *     @OA\Property(property="description_en", type="string", example="American sportswear brand"),
 *     @OA\Property(property="description_ar", type="string", example="علامة تجارية أمريكية للملابس الرياضية"),
 *     @OA\Property(property="logo", type="string", example="brands/nike.png"),
 *  @OA\Property(property="category_id", type="integer", example=2),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class SubCategoryController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/subcategories",
 *     summary="Get all subcategories with pagination",
 *     tags={"SubCategories"},
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=false,
 *         description="Page number",
 *         @OA\Schema(type="integer", default=1)
 *     ),
 *     @OA\Parameter(
 *         name="pageSize",
 *         in="query",
 *         required=false,
 *         description="Number of items per page",
 *         @OA\Schema(type="integer", default=10)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Paginated list of subcategories",
 *         @OA\JsonContent(
 *             @OA\Property(property="pageNumber", type="integer", example=1),
 *             @OA\Property(property="pageSize", type="integer", example=10),
 *             @OA\Property(property="total", type="integer", example=100),
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
        $pageSize = $request->input('pageSize', 10);
        $pageNumber = $request->input('pageNumber', 1);

        $subcategories = SubCategory::paginate($pageSize, ['*'], 'page', $pageNumber);

        return response()->json([
            'pageNumber' => $subcategories->currentPage(),
            'pageSize'   => $subcategories->perPage(),
            'total'      => $subcategories->total(),
            'data'       => $subcategories->items(),
        ]);
    }


    /**
     * @OA\Get(
     *     path="/api/subcategories/{id}",
     *     summary="Get a single subcategories",
     *     tags={"SubCategories"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="SubCategory found", @OA\JsonContent(ref="#/components/schemas/SubCategory")),
     *     @OA\Response(response=404, description="SubCategory not found")
     * )
     */
    public function show($id)
    {
        $subcategories = SubCategory::find($id);
        if (!$subcategories) {
            return response()->json(['message' => 'subcategories not found'], 404);
        }
        return $subcategories;
    }

    /**
     * @OA\Post(
     *     path="/api/subcategories",
     *     summary="Create a new brand",
     *     tags={"SubCategories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name_en", "name_ar","description_en","description_ar","category_id"},
     *                 @OA\Property(property="name_en", type="string"),
     *                 @OA\Property(property="name_ar", type="string"),
     *                 @OA\Property(property="description_en", type="string"),
     *                 @OA\Property(property="description_ar", type="string"),
     *                 @OA\Property(property="logo", type="file"),
     *  @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="status", type="boolean")
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
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'status' => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('subcategories', 'public');
        }

        $subcategories = SubCategory::create($validated);
        return response()->json($subcategories, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/subcategories/{id}",
     *     summary="Update a subcategories",
     *     tags={"SubCategories"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name_en", "name_ar","description_en","description_ar","category_id"},
     *                 @OA\Property(property="name_en", type="string"),
     *                 @OA\Property(property="name_ar", type="string"),
     *                 @OA\Property(property="description_en", type="string"),
     *                 @OA\Property(property="description_ar", type="string"),
     *                 @OA\Property(property="logo", type="file"),
     *  @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="status", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="subcategories updated", @OA\JsonContent(ref="#/components/schemas/SubCategory")),
     *     @OA\Response(response=404, description="subcategories not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $subcategories = SubCategory::find($id);
        if (!$subcategories) {
            return response()->json(['message' => 'subcategories not found'], 404);
        }

        $validated = $request->validate([
            'name_en' => 'required|string',
            'name_ar' => 'required|string',
            'description_en' => 'required|string',
            'description_ar' => 'required|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
               'category_id' => 'required|exists:categories,id',
            'status' => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            if ($subcategories->logo) {
                Storage::disk('public')->delete($subcategories->logo);
            }
            $validated['logo'] = $request->file('logo')->store('subcategories', 'public');
        }

        $brand->update($validated);
        return response()->json($subcategories);
    }

    /**
     * @OA\Delete(
     *     path="/api/subcategories/{id}",
     *     summary="Delete a subcategories",
     *     tags={"SubCategories"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="subcategories deleted"),
     *     @OA\Response(response=404, description="subcategories not found")
     * )
     */
    public function destroy($id)
    {
        $subcategories = SubCategory::find($id);
        if (!$subcategories) {
            return response()->json(['message' => 'subcategories not found'], 404);
        }

        if ($subcategories->logo) {
            Storage::disk('public')->delete($subcategories->logo);
        }

        $subcategories->delete();
        return response()->json(['message' => 'subcategories deleted']);
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
 *         description="Paginated list of subcategories by category",
 *         @OA\JsonContent(
 *             @OA\Property(property="pageNumber", type="integer"),
 *             @OA\Property(property="pageSize", type="integer"),
 *             @OA\Property(property="totalPageNumber", type="integer"),
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SubCategory"))
 *         )
 *     )
 * )
 */
public function getByCategory($categoryId)
{
    $subcategories = SubCategory::where('category_id', $categoryId)->paginate(10);

    return response()->json([
        'pageNumber' => $subcategories->currentPage(),
        'pageSize' => $subcategories->perPage(),
        'totalPageNumber' => $subcategories->lastPage(),
        'data' => $subcategories->items(),
    ]);
}
}
