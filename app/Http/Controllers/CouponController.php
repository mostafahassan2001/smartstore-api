<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CouponController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Coupons retrieved successfully',
            'data'    => Coupon::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'            => 'required|string|unique:coupons,code',
            'description'     => 'nullable|string',
            'discount_type'   => 'required|in:percentage,fixed',
            'discount_value'  => 'required|numeric|min:0',
            'max_uses'        => 'nullable|integer|min:1',
            'start_date'      => 'nullable|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'is_active'       => 'boolean',
        ]);

        $coupon = Coupon::create($validated);

        return response()->json([
            'message' => 'Coupon created successfully',
            'data'    => $coupon
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }

        $validated = $request->validate([
            'code'            => 'nullable|string|unique:coupons,code,' . $coupon->id,
            'description'     => 'nullable|string',
            'discount_type'   => 'nullable|in:percentage,fixed',
            'discount_value'  => 'nullable|numeric|min:0',
            'max_uses'        => 'nullable|integer|min:1',
            'start_date'      => 'nullable|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'is_active'       => 'boolean',
        ]);

        $coupon->update($validated);

        return response()->json([
            'message' => 'Coupon updated successfully',
            'data'    => $coupon
        ]);
    }

    public function destroy($id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }

        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted successfully']);
    }

    public function check(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $coupon = Coupon::where('code', $request->code)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->whereColumn('used_count', '<', 'max_uses')
            ->first();

        if (!$coupon) {
            return response()->json(['message' => 'Invalid or expired coupon'], 400);
        }

        return response()->json([
            'valid'          => true,
            'discount_type'  => $coupon->discount_type,
            'discount_value' => $coupon->discount_value,
            'data'           => $coupon,
            'message'        => 'Coupon is valid'
        ]);
    }
}
