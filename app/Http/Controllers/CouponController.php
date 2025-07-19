<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @OA\Schema(
 *     schema="Coupon",
 *     type="object",
 *     required={"code", "discount_type", "discount_value"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="SAVE10"),
 *     @OA\Property(property="description", type="string", example="10% off your order"),
 *     @OA\Property(property="discount_type", type="string", enum={"percentage", "fixed"}, example="percentage"),
 *     @OA\Property(property="discount_value", type="number", format="float", example=10.00),
 *     @OA\Property(property="max_uses", type="integer", example=100),
 *     @OA\Property(property="used_count", type="integer", example=10),
 *     @OA\Property(property="start_date", type="string", format="date-time", example="2025-07-01T00:00:00Z"),
 *     @OA\Property(property="end_date", type="string", format="date-time", example="2025-08-01T00:00:00Z"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 * )
 */
class CouponController extends Controller
{
    /**
     * Get all coupons
     *
     * @OA\Get(
     *     path="/api/coupons",
     *     summary="Get all coupons",
     *     tags={"Coupons"},
     *     @OA\Response(
     *         response=200,
     *         description="List of coupons",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Coupon"))
     *     )
     * )
     */
    public function index()
    {
        return Coupon::all();
    }

    /**
     * Store new coupon
     *
     * @OA\Post(
     *     path="/api/coupons",
     *     summary="Create a new coupon",
     *     tags={"Coupons"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code", "discount_type", "discount_value"},
     *             @OA\Property(property="code", type="string", example="SAVE10"),
     *             @OA\Property(property="description", type="string", example="10% off your order"),
     *             @OA\Property(property="discount_type", type="string", enum={"percentage", "fixed"}, example="percentage"),
     *             @OA\Property(property="discount_value", type="number", format="float", example=10.00),
     *             @OA\Property(property="max_uses", type="integer", example=100),
     *             @OA\Property(property="start_date", type="string", format="date-time", example="2025-07-01T00:00:00Z"),
     *             @OA\Property(property="end_date", type="string", format="date-time", example="2025-08-01T00:00:00Z"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Coupon created",
     *         @OA\JsonContent(ref="#/components/schemas/Coupon")
     *     )
     * )
     */
    public function store(Request $request)
    {
        // ...
    }

    /**
     * Update coupon
     *
     * @OA\Put(
     *     path="/api/coupons/{id}",
     *     summary="Update a coupon",
     *     tags={"Coupons"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true, @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="string", example="SAVE15"),
     *             @OA\Property(property="description", type="string", example="15% off your order"),
     *             @OA\Property(property="discount_type", type="string", enum={"percentage", "fixed"}, example="fixed"),
     *             @OA\Property(property="discount_value", type="number", format="float", example=15.00),
     *             @OA\Property(property="max_uses", type="integer", example=50),
     *             @OA\Property(property="start_date", type="string", format="date-time", example="2025-07-10T00:00:00Z"),
     *             @OA\Property(property="end_date", type="string", format="date-time", example="2025-08-10T00:00:00Z"),
     *             @OA\Property(property="is_active", type="boolean", example=false),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon updated",
     *         @OA\JsonContent(ref="#/components/schemas/Coupon")
     *     ),
     *     @OA\Response(response=404, description="Coupon not found")
     * )
     */
    public function update(Request $request, $id)
    {
        // ...
    }

    /**
     * Delete coupon
     *
     * @OA\Delete(
     *     path="/api/coupons/{id}",
     *     summary="Delete a coupon",
     *     tags={"Coupons"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true, @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Coupon deleted"),
     *     @OA\Response(response=404, description="Coupon not found")
     * )
     */
    public function destroy($id)
    {
        // ...
    }

    /**
     * Check coupon validity
     *
     * @OA\Get(
     *     path="/api/coupons/check",
     *     summary="Check if a coupon is valid",
     *     tags={"Coupons"},
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", example="SAVE10")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon is valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="valid", type="boolean", example=true),
     *             @OA\Property(property="discount_type", type="string", example="percentage"),
     *             @OA\Property(property="discount_value", type="number", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or expired coupon",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Invalid or expired coupon"))
     *     )
     * )
     */
    public function check(Request $request)
    {
        // ...
    }
}
