<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="Brand",
 *     type="object",
 *     title="Brand",
 *     required={"id", "name_en", "name_ar"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name_en", type="string", example="Nike"),
 *     @OA\Property(property="name_ar", type="string", example="نايك"),
 *     @OA\Property(property="description_en", type="string", example="American sportswear brand"),
 *     @OA\Property(property="description_ar", type="string", example="علامة تجارية أمريكية للملابس الرياضية"),
 *     @OA\Property(property="logo", type="string", example="brands/nike.png"),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class BrandController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/brands",
     *     summary="Get all brands",
     *     tags={"Brands"},
     *     @OA\Response(
     *         response=200,
     *         description="List of brands",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Brand"))
     *     )
     * )
     */
    public function index()
    {
        return Brand::all();
    }

    /**
     * @OA\Get(
     *     path="/api/brands/{id}",
     *     summary="Get a single brand",
     *     tags={"Brands"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Brand found", @OA\JsonContent(ref="#/components/schemas/Brand")),
     *     @OA\Response(response=404, description="Brand not found")
     * )
     */
    public function show($id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }
        return $brand;
    }

    /**
     * @OA\Post(
     *     path="/api/brands",
     *     summary="Create a new brand",
     *     tags={"Brands"},
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
     *                 @OA\Property(property="logo", type="file"),
     *                 @OA\Property(property="status", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Brand created", @OA\JsonContent(ref="#/components/schemas/Brand"))
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
            'status' => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand = Brand::create($validated);
        return response()->json($brand, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/brands/{id}",
     *     summary="Update a brand",
     *     tags={"Brands"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name_en", type="string"),
     *                 @OA\Property(property="name_ar", type="string"),
     *                 @OA\Property(property="description_en", type="string"),
     *                 @OA\Property(property="description_ar", type="string"),
     *                 @OA\Property(property="logo", type="file"),
     *                 @OA\Property(property="status", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Brand updated", @OA\JsonContent(ref="#/components/schemas/Brand")),
     *     @OA\Response(response=404, description="Brand not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        $validated = $request->validate([
            'name_en' => 'sometimes|required|string',
            'name_ar' => 'sometimes|required|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'status' => 'boolean',
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
     * @OA\Delete(
     *     path="/api/brands/{id}",
     *     summary="Delete a brand",
     *     tags={"Brands"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Brand deleted"),
     *     @OA\Response(response=404, description="Brand not found")
     * )
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
