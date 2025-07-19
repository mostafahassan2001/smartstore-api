<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/cart",
     *     summary="Get all items in the cart",
     *     tags={"Cart"},
     *     @OA\Response(
     *         response=200,
     *         description="List of cart items"
     *     )
     * )
     */
    public function index()
    {
        $cartItems = Cart::where('user_id', Auth::id())->with('product')->get();

        return response()->json([
            'message' => 'Cart items retrieved successfully',
            'data'    => $cartItems
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/cart",
     *     summary="Add a product to the cart",
     *     tags={"Cart"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item added to cart"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1'
        ]);

        $cart = Cart::where('user_id', Auth::id())
                    ->where('product_id', $validated['product_id'])
                    ->first();

        if ($cart) {
            $cart->quantity += $validated['quantity'];
            $cart->save();
        } else {
            $cart = Cart::create([
                'user_id'    => Auth::id(),
                'product_id' => $validated['product_id'],
                'quantity'   => $validated['quantity'],
            ]);
        }

        return response()->json([
            'message' => 'Item added to cart successfully',
            'data'    => $cart->load('product')
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/cart/{id}",
     *     summary="Update quantity of a cart item",
     *     tags={"Cart"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart item updated"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();

        $cart->update(['quantity' => $validated['quantity']]);

        return response()->json([
            'message' => 'Cart item updated successfully',
            'data'    => $cart->load('product')
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart/{id}",
     *     summary="Remove item from cart",
     *     tags={"Cart"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item removed from cart"
     *     )
     * )
     */
    public function destroy($id)
    {
        $cart = Cart::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();

        $cart->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }

    /**
     * @OA\Post(
     *     path="/api/cart/clear",
     *     summary="Clear the entire cart",
     *     tags={"Cart"},
     *     @OA\Response(
     *         response=200,
     *         description="Cart cleared"
     *     )
     * )
     */
    public function clear()
    {
        Cart::where('user_id', Auth::id())->delete();
        session()->forget('cart_coupon');

        return response()->json(['message' => 'Cart cleared successfully']);
    }

    /**
     * @OA\Post(
     *     path="/api/cart/apply-coupon",
     *     summary="Apply a discount coupon to the cart",
     *     tags={"Cart"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code"},
     *             @OA\Property(property="code", type="string", example="DISCOUNT10")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon applied"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or expired coupon"
     *     )
     * )
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

        return response()->json([
            'message' => 'Coupon applied successfully',
            'coupon'  => $coupon
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/cart/remove-coupon",
     *     summary="Remove applied discount coupon from the cart",
     *     tags={"Cart"},
     *     @OA\Response(
     *         response=200,
     *         description="Coupon removed"
     *     )
     * )
     */
    public function removeDiscount()
    {
        session()->forget('cart_coupon');

        return response()->json(['message' => 'Coupon removed successfully']);
    }

    /**
     * @OA\Get(
     *     path="/api/cart/total",
     *     summary="Get cart subtotal, discount, and total",
     *     tags={"Cart"},
     *     @OA\Response(
     *         response=200,
     *         description="Cart total",
     *         @OA\JsonContent(
     *             @OA\Property(property="subtotal", type="number", example=150.00),
     *             @OA\Property(property="discount", type="number", example=15.00),
     *             @OA\Property(property="total", type="number", example=135.00)
     *         )
     *     )
     * )
     */
    public function total()
    {
        $cartItems = Cart::where('user_id', Auth::id())->with('product')->get();

        $subtotal = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

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
            'message'  => 'Cart total calculated successfully',
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'total'    => round($total, 2)
        ]);
    }
}
