<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
//
/**
 * @OA\Info(
 *     title="Smart Store API",
 *     version="1.0.0",
 *     description="API documentation for Smart Store"
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Get all products",
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of products",
     *         @OA\JsonContent(
     *             @OA\Property(property="pageNumber", type="integer", example=1),
     *             @OA\Property(property="pageSize", type="integer", example=10),
     *             @OA\Property(property="totalPageNumber", type="integer", example=5),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Product")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $products = Product::paginate(10);

        return response()->json([
            'pageNumber' => $products->currentPage(),
            'pageSize' => $products->perPage(),
            'totalPageNumber' => $products->lastPage(),
            'data' => $products->items(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get single product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     )
     * )
     */
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return $product;
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create a new product",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name_en", "name_ar", "price", "category_id", "brand_id"},
     *                 @OA\Property(property="name_en", type="string"),
     *                 @OA\Property(property="name_ar", type="string"),
     *                 @OA\Property(property="description_en", type="string"),
     *                 @OA\Property(property="description_ar", type="string"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="stock_quantity", type="integer"),
     *                 @OA\Property(property="status", type="boolean"),
     *                 @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="sub_category_id", type="integer"),
     *                 @OA\Property(property="brand_id", type="integer"),
     *                 @OA\Property(property="colors[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="sizes[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="image", type="file")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Product created", @OA\JsonContent(ref="#/components/schemas/Product"))
     * )
     */
    public function store(Request $request)
    {
      $validated = $request->validate([
    'name_en' => 'required|string',
    'name_ar' => 'required|string',
    'description_en' => 'required|string',
    'description_ar' => 'required|string',
    'price' => 'required|numeric|min:0',
    'stock_quantity' => 'required|integer|min:0',
    'status' => 'required|boolean',
    'category_id' => 'required|exists:categories,id',
    'sub_category_id' => 'nullable|exists:sub_categories,id',
    'brand_id' => 'required|exists:brands,id',
    'colors' => 'nullable|array',
    'colors.*' => 'string',
    'sizes' => 'nullable|array',
    'sizes.*' => 'string',
    'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
]);


        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['colors'] = isset($validated['colors']) ? json_encode($validated['colors']) : null;
        $validated['sizes'] = isset($validated['sizes']) ? json_encode($validated['sizes']) : null;

        $product = Product::create($validated);
        return response()->json($product, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Update a product",
     *     tags={"Products"},
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
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="stock_quantity", type="integer"),
     *                 @OA\Property(property="status", type="boolean"),
     *                 @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="sub_category_id", type="integer"),
     *                 @OA\Property(property="brand_id", type="integer"),
     *                 @OA\Property(property="colors[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="sizes[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="image", type="file")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Product updated", @OA\JsonContent(ref="#/components/schemas/Product"))
     * )
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

    $validated = $request->validate([
    'name_en' => 'required|string',
    'name_ar' => 'required|string',
    'description_en' => 'required|string',
    'description_ar' => 'required|string',
    'price' => 'required|numeric|min:0',
    'stock_quantity' => 'required|integer|min:0',
    'status' => 'required|boolean',
    'category_id' => 'required|exists:categories,id',
    'sub_category_id' => 'nullable|exists:sub_categories,id',
    'brand_id' => 'required|exists:brands,id',
    'colors' => 'nullable|array',
    'colors.*' => 'string',
    'sizes' => 'nullable|array',
    'sizes.*' => 'string',
    'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
]);


        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        if (isset($validated['colors'])) {
            $validated['colors'] = json_encode($validated['colors']);
        }
        if (isset($validated['sizes'])) {
            $validated['sizes'] = json_encode($validated['sizes']);
        }

        $product->update($validated);
        return response()->json($product);
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Delete a product",
     *     tags={"Products"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Product deleted")
     * )
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        return response()->json(['message' => 'Product deleted']);
    }
  /**
 * Get products by category ID (paginated)
 *
 * @OA\Get(
 *     path="/api/product/category/{categoryId}",
 *     summary="Get products by category ID",
 *     tags={"Products"},
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
 *         description="Paginated list of products by category",
 *         @OA\JsonContent(
 *             @OA\Property(property="pageNumber", type="integer"),
 *             @OA\Property(property="pageSize", type="integer"),
 *             @OA\Property(property="totalPageNumber", type="integer"),
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product"))
 *         )
 *     )
 * )
 */
public function getByCategory($categoryId)
{
    $products = Product::where('category_id', $categoryId)->paginate(10);

    return response()->json([
        'pageNumber' => $products->currentPage(),
        'pageSize' => $products->perPage(),
        'totalPageNumber' => $products->lastPage(),
        'data' => $products->items(),
    ]);
}

/**
 * Get products by brand ID (paginated)
 *
 * @OA\Get(
 *     path="/api/product/brand/{brandId}",
 *     summary="Get products by brand ID",
 *     tags={"Products"},
 *     @OA\Parameter(
 *         name="brandId",
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
 *         description="Paginated list of products by brand",
 *         @OA\JsonContent(
 *             @OA\Property(property="pageNumber", type="integer"),
 *             @OA\Property(property="pageSize", type="integer"),
 *             @OA\Property(property="totalPageNumber", type="integer"),
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product"))
 *         )
 *     )
 * )
 */
public function getByBrand($brandId)
{
    $products = Product::where('brand_id', $brandId)->paginate(10);

    return response()->json([
        'pageNumber' => $products->currentPage(),
        'pageSize' => $products->perPage(),
        'totalPageNumber' => $products->lastPage(),
        'data' => $products->items(),
    ]);
}
  
}

