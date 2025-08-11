<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CouponController extends Controller
{
    // جلب كل الكوبونات
    public function index()
    {
        return Coupon::all();
    }

    // إنشاء كوبون جديد
    public function store(Request $request)
    {
        // تحقق من صحة البيانات الواردة
        $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric',
            'max_uses' => 'nullable|integer',
            'used_count' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_active' => 'required|boolean',
            'description' => 'nullable|string',
        ]);

        // انشئ الكوبون
        $coupon = Coupon::create([
            'code' => $request->code,
            'description' => $request->description,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'max_uses' => $request->max_uses ?? 0,
            'used_count' => $request->used_count ?? 0,
            'start_date' => $request->start_date ? Carbon::parse($request->start_date) : null,
            'end_date' => $request->end_date ? Carbon::parse($request->end_date) : null,
            'is_active' => $request->is_active,
        ]);

        return response()->json($coupon, 201);
    }

    // تحديث كوبون
    public function update(Request $request, $id)
    {
        $coupon = Coupon::find($id);
        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }

        $request->validate([
            'code' => 'sometimes|required|string|unique:coupons,code,' . $id,
            'discount_type' => 'sometimes|required|in:percentage,fixed',
            'discount_value' => 'sometimes|required|numeric',
            'max_uses' => 'nullable|integer',
            'used_count' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_active' => 'sometimes|required|boolean',
            'description' => 'nullable|string',
        ]);

        $coupon->update([
            'code' => $request->code ?? $coupon->code,
            'description' => $request->description ?? $coupon->description,
            'discount_type' => $request->discount_type ?? $coupon->discount_type,
            'discount_value' => $request->discount_value ?? $coupon->discount_value,
            'max_uses' => $request->max_uses ?? $coupon->max_uses,
            'used_count' => $request->used_count ?? $coupon->used_count,
            'start_date' => $request->start_date ? Carbon::parse($request->start_date) : $coupon->start_date,
            'end_date' => $request->end_date ? Carbon::parse($request->end_date) : $coupon->end_date,
            'is_active' => $request->is_active ?? $coupon->is_active,
        ]);

        return response()->json($coupon, 200);
    }

    // حذف كوبون
    public function destroy($id)
    {
        $coupon = Coupon::find($id);
        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }

        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted'], 200);
    }

    // التحقق من صلاحية الكوبون
    public function check(Request $request)
    {
        $code = $request->query('code');

        if (!$code) {
            return response()->json(['message' => 'Coupon code is required'], 400);
        }

        $coupon = Coupon::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return response()->json(['message' => 'Invalid or expired coupon'], 400);
        }

        $now = Carbon::now();

        if (($coupon->start_date && $now->lt($coupon->start_date)) ||
            ($coupon->end_date && $now->gt($coupon->end_date))) {
            return response()->json(['message' => 'Invalid or expired coupon'], 400);
        }

        if ($coupon->max_uses > 0 && $coupon->used_count >= $coupon->max_uses) {
            return response()->json(['message' => 'Coupon usage limit reached'], 400);
        }

        return response()->json([
            'valid' => true,
            'discount_type' => $coupon->discount_type,
            'discount_value' => $coupon->discount_value,
        ], 200);
    }
}
