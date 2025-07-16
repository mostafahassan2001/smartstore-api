<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="Banner",
 *     type="object",
 *     title="Banner",
 *     required={"id", "title", "title_ar", "image"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Summer Sale"),
 *     @OA\Property(property="title_ar", type="string", example="تخفيضات الصيف"),
 *     @OA\Property(property="description", type="string", example="Huge discounts this summer"),
 *     @OA\Property(property="description_ar", type="string", example="خصومات كبيرة هذا الصيف"),
 *     @OA\Property(property="image", type="string", example="banners/summer.jpg"),
 *     @OA\Property(property="link_url", type="string", example="https://example.com"),
 *     @OA\Property(property="order", type="integer", example=1),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class BannerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/banners",
     *     summary="Get all banners",
     *     tags={"Banners"},
     *     @OA\Response(
     *         response=200,
     *         description="List of banners",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Banner"))
     *     )
     * )
     */
    public function index()
    {
        return Banner::all();
    }

    /**
     * @OA\Get(
     *     path="/api/banners/{id}",
     *     summary="Get a single banner",
     *     tags={"Banners"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Banner found", @OA\JsonContent(ref="#/components/schemas/Banner")),
     *     @OA\Response(response=404, description="Banner not found")
     * )
     */
    public function show($id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['message' => 'Banner not found'], 404);
        }
        return $banner;
    }

    /**
     * @OA\Post(
     *     path="/api/banners",
     *     summary="Create a new banner",
     *     tags={"Banners"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "title_ar"},
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="title_ar", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="description_ar", type="string"),
     *                 @OA\Property(property="image", type="file"),
     *                 @OA\Property(property="link_url", type="string"),
     *                 @OA\Property(property="order", type="integer"),
     *                 @OA\Property(property="is_active", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Banner created", @OA\JsonContent(ref="#/components/schemas/Banner"))
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'title_ar' => 'required|string',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'image' => 'required|image|mimes:jpg,jpeg,png,svg|max:2048',
            'link_url' => 'nullable|url',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['image'] = $request->file('image')->store('banners', 'public');

        $banner = Banner::create($validated);
        return response()->json($banner, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/banners/{id}",
     *     summary="Update a banner",
     *     tags={"Banners"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="title_ar", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="description_ar", type="string"),
     *                 @OA\Property(property="image", type="file"),
     *                 @OA\Property(property="link_url", type="string"),
     *                 @OA\Property(property="order", type="integer"),
     *                 @OA\Property(property="is_active", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Banner updated", @OA\JsonContent(ref="#/components/schemas/Banner"))
     * )
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['message' => 'Banner not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string',
            'title_ar' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'link_url' => 'nullable|url',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $validated['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($validated);
        return response()->json($banner);
    }

    /**
     * @OA\Delete(
     *     path="/api/banners/{id}",
     *     summary="Delete a banner",
     *     tags={"Banners"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Banner deleted"),
     *     @OA\Response(response=404, description="Banner not found")
     * )
     */
    public function destroy($id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['message' => 'Banner not found'], 404);
        }

        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();
        return response()->json(['message' => 'Banner deleted']);
    }
}
