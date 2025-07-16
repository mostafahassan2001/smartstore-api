<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Get all items in the cart
     */
    public function index()
    {
        return Cart::where('user_id', Auth::id())->with('product')->get();
    }

    /**
     * Add a product to the cart
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::updateOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $validated['product_id']],
            ['quantity' => \DB::raw("quantity + {$validated['quantity']}")]
        );

        return response()->json(['message' => 'Item added to cart', 'data' => $cart]);
    }

    /**
     * Update quantity of a cart item
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $cart->update(['quantity' => $validated['quantity']]);

        return response()->json(['message' => 'Cart item updated']);
    }

    /**
     * Remove item from cart
     */
    public function destroy($id)
    {
        $cart = Cart::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $cart->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }

    /**
     * Clear the cart
     *
     * DELETE /api/cart/clear
     */
    public function clear()
    {
        Cart::where('user_id', Auth::id())->delete();

        session()->forget('cart_coupon');

        return response()->json(['message' => 'Cart cleared']);
    }

    /**
     * Apply a discount coupon to the cart
     *
     * POST /api/cart/discount
     */
    public function applyDiscount(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string'
        ]);

        $coupon = Coupon::where('code', $validated['code'])
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

        session(['cart_coupon' => $coupon]);

        return response()->json(['message' => 'Coupon applied', 'coupon' => $coupon]);
    }

    /**
     * Remove the applied discount coupon from the cart
     *
     * DELETE /api/cart/discount
     */
    public function removeDiscount()
    {
        session()->forget('cart_coupon');
        return response()->json(['message' => 'Coupon removed']);
    }

    /**
     * Get cart total (with or without discount)
     *
     * GET /api/cart/total
     */
    public function total()
    {
        $cartItems = Cart::where('user_id', Auth::id())->with('product')->get();

        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        $discount = 0;

        if (session()->has('cart_coupon')) {
            $coupon = session('cart_coupon');
            if ($coupon['discount_type'] === 'percentage') {
                $discount = ($coupon['discount_value'] / 100) * $subtotal;
            } else {
                $discount = $coupon['discount_value'];
            }
        }

        $total = max($subtotal - $discount, 0);

        return response()->json([
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'total' => round($total, 2)
        ]);
    }
}
