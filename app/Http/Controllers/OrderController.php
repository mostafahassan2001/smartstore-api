<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Get all orders
     *
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Get all orders",
     *     tags={"Orders"},
     *     @OA\Response(
     *         response=200,
     *         description="List of orders"
     *     )
     * )
     */
    public function index()
    {
        return Order::with(['user', 'address'])->get();
    }

    /**
     * Store new order
     *
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Create a new order",
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "total"},
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="address_id", type="integer"),
     *             @OA\Property(property="total", type="number"),
     *             @OA\Property(property="payment_method", type="string", example="cash")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'address_id' => 'nullable|exists:addresses,id',
            'total' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
        ]);

        $order = Order::create([
            'user_id' => $validated['user_id'],
            'address_id' => $validated['address_id'] ?? null,
            'total' => $validated['total'],
            'payment_method' => $validated['payment_method'] ?? 'cash',
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Order created', 'data' => $order], 201);
    }

    /**
     * Show single order
     *
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Get a single order",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Order details"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function show($id)
    {
        $order = Order::with(['user', 'address'])->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order);
    }

    /**
     * Update order status
     *
     * @OA\Put(
     *     path="/api/orders/{id}",
     *     summary="Update order status",
     *     tags={"Orders"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", example="processing")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order status updated")
     * )
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order->status = $validated['status'];
        $order->save();

        return response()->json(['message' => 'Order status updated']);
    }

    /**
     * Delete an order
     *
     * @OA\Delete(
     *     path="/api/orders/{id}",
     *     summary="Delete an order",
     *     tags={"Orders"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Order deleted")
     * )
     */
    public function destroy($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->delete();
        return response()->json(['message' => 'Order deleted']);
    }

    /**
     * Update tracking number
     *
     * @OA\Put(
     *     path="/api/orders/{id}/tracking",
     *     summary="Update tracking number for an order",
     *     tags={"Orders"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tracking_number"},
     *             @OA\Property(property="tracking_number", type="string", example="1234567890")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Tracking number updated"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function updateTracking(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $validated = $request->validate([
            'tracking_number' => 'required|string|max:255',
        ]);

        $order->tracking_number = $validated['tracking_number'];
        $order->save();

        return response()->json(['message' => 'Tracking number updated']);
    }
}
