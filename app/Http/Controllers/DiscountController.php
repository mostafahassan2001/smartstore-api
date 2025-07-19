<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @OA\Schema(
 *     schema="Discount",
 *     type="object",
 *     required={"name", "name_ar", "description", "description_ar", "discount_code", "discount_percentage", "start_date", "end_date"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Summer Sale"),
 *     @OA\Property(property="name_ar", type="string", example="تخفيضات الصيف"),
 *     @OA\Property(property="description", type="string", example="Up to 50% off"),
 *     @OA\Property(property="description_ar", type="string", example="خصومات تصل إلى 50%"),
 *     @OA\Property(property="discount_code", type="string", example="SUMMER50"),
 *     @OA\Property(property="discount_percentage", type="number", format="float", example=10.00),
 *     @OA\Property(property="start_date", type="string", format="date-time", example="2025-07-01T00:00:00Z"),
 *     @OA\Property(property="end_date", type="string", format="date-time", example="2025-08-01T00:00:00Z"),
 *     @OA\Property(property="is_active", type="boolean", example=true)
 * )
 */
class DiscountController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/discounts",
     *     summary="Get all discounts",
     *     tags={"Discounts"},
     *     @OA\Response(
     *         response=200,
     *         description="List of discounts",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Discount"))
     *     )
     * )
     */
    public function index()
    {
        return Discount::all();
    }

    /**
     * @OA\Post(
     *     path="/api/discounts",
     *     summary="Create new discount",
     *     tags={"Discounts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "name_ar", "description", "description_ar", "discount_code", "discount_percentage", "start_date", "end_date"},
     *             @OA\Property(property="name", type="string", example="Summer Sale"),
     *             @OA\Property(property="name_ar", type="string", example="تخفيضات الصيف"),
     *             @OA\Property(property="description", type="string", example="Save up to 50%"),
     *             @OA\Property(property="description_ar", type="string", example="وفر حتى 50%"),
     *             @OA\Property(property="discount_code", type="string", example="SUMMER50"),
     *             @OA\Property(property="discount_percentage", type="number", example=15.5),
     *             @OA\Property(property="start_date", type="string", format="date-time", example="2025-07-01T00:00:00Z"),
     *             @OA\Property(property="end_date", type="string", format="date-time", example="2025-08-01T00:00:00Z"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *         )
     *     ),
     *     @OA\Response(response=201, description="Discount created", @OA\JsonContent(ref="#/components/schemas/Discount"))
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'name_ar' => 'required|string',
            'description' => 'required|string',
            'description_ar' => 'required|string',
            'discount_code' => 'required|string|unique:discounts,discount_code',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        $discount = Discount::create($validated);
        return response()->json(['message' => 'Discount created', 'data' => $discount], 201);
    }
    /**
     * @OA\Get(
     *     path="/api/discounts/{id}",
     *     summary="Get a single discount",
     *     tags={"Discounts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Discount ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Discount details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Summer Sale"),
     *             @OA\Property(property="name_ar", type="string", example="تخفيضات الصيف"),
     *             @OA\Property(property="description", type="string", example="10% off all items"),
     *             @OA\Property(property="description_ar", type="string", example="خصم 10٪ على جميع المنتجات"),
     *             @OA\Property(property="discount_code", type="string", example="SUMMER2025"),
     *             @OA\Property(property="discount_percentage", type="number", example=10.00),
     *             @OA\Property(property="start_date", type="string", format="date-time", example="2025-07-01T00:00:00"),
     *             @OA\Property(property="end_date", type="string", format="date-time", example="2025-07-31T23:59:59"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Discount not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Discount not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $discount = Discount::find($id);

        if (!$discount) {
            return response()->json(['message' => 'Discount not found'], 404);
        }

        return response()->json($discount);
    }

    /**
     * @OA\Put(
     *     path="/api/discounts/{id}",
     *     summary="Update discount",
     *     tags={"Discounts"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="name_ar", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="description_ar", type="string"),
     *             @OA\Property(property="discount_code", type="string"),
     *             @OA\Property(property="discount_percentage", type="number"),
     *             @OA\Property(property="start_date", type="string", format="date-time"),
     *             @OA\Property(property="end_date", type="string", format="date-time"),
     *             @OA\Property(property="is_active", type="boolean"),
     *         )
     *     ),
     *     @OA\Response(response=200, description="Discount updated"),
     *     @OA\Response(response=404, description="Discount not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $discount = Discount::find($id);
        if (!$discount) {
            return response()->json(['message' => 'Discount not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'name_ar' => 'required|string',
            'description' => 'required|string',
            'description_ar' => 'required|string',
            'discount_code' => 'required|string|unique:discounts,discount_code,' . $id,
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        $discount->update($validated);
        return response()->json(['message' => 'Discount updated']);
    }

    /**
     * @OA\Delete(
     *     path="/api/discounts/{id}",
     *     summary="Delete discount",
     *     tags={"Discounts"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Discount deleted"),
     *     @OA\Response(response=404, description="Discount not found")
     * )
     */
    public function destroy($id)
    {
        $discount = Discount::find($id);
        if (!$discount) {
            return response()->json(['message' => 'Discount not found'], 404);
        }

        $discount->delete();
        return response()->json(['message' => 'Discount deleted']);
    }

    /**
     * @OA\Get(
     *     path="/api/discounts/check",
     *     summary="Check discount code",
     *     tags={"Discounts"},
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", example="SUMMER50")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Valid discount",
     *         @OA\JsonContent(
     *             @OA\Property(property="valid", type="boolean", example=true),
     *             @OA\Property(property="discount_percentage", type="number", example=15.5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Discount expired or invalid",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Discount expired or invalid"))
     *     )
     * )
     */
    public function check(Request $request)
    {
        $code = $request->query('code');

        $discount = Discount::where('discount_code', $code)
            ->where('is_active', true)
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->first();

        if (!$discount) {
            return response()->json(['message' => 'Discount expired or invalid'], 400);
        }

        return response()->json([
            'valid' => true,
            'discount_percentage' => $discount->discount_percentage,
        ]);
    }
}
