<?php

namespace App\Http\Controllers;

use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="SubCategories",
 *     type="object",
 *     title="SubCategories",
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
     *     summary="Get paginated list of SubCategories",
     *     tags={"SubCategories"},
     *     @OA\Parameter(name="pageNumber", in="query", @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="pageSize", in="query", @OA\Schema(type="integer", default=10)),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="pageNumber", type="integer"),
     *             @OA\Property(property="pageSize", type="integer"),
     *             @OA\Property(property="totalPageNumber", type="integer"),
     *             @OA\Property(property="totalItems", type="integer"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SubCategories"))
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
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $sub = SubCategory::find($id);
        if (!$sub) {
            return response()->json(['message' => 'SubCategory not found'], 404);
        }
        return response()->json($sub);
    }

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
