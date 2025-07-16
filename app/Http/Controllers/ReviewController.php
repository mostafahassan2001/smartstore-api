<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
/**
 * @OA\Schema(
 *     schema="Review",
 *     type="object",
 *     title="Review",
 *     required={"user_id", "product_id", "rating"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=2),
 *     @OA\Property(property="product_id", type="integer", example=5),
 *     @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=4),
 *     @OA\Property(property="comment", type="string", example="Nice product."),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-16T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-16T12:00:00Z")
 * )
 */

class ReviewController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/reviews",
     *     summary="Get all reviews",
     *     tags={"Reviews"},
     *     @OA\Response(
     *         response=200,
     *         description="List of reviews",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Review"))
     *     )
     * )
     */
    public function index()
    {
        return Review::with(['user', 'product'])->get();
    }

    /**
     * @OA\Post(
     *     path="/api/reviews",
     *     summary="Create new review",
     *     tags={"Reviews"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "product_id", "rating"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="product_id", type="integer", example=3),
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=4),
     *             @OA\Property(property="comment", type="string", example="Great product!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Review created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review added successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Review")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review = Review::create($validated);

        return response()->json(['message' => 'Review added successfully', 'data' => $review], 201);
    }
/**
 * Get a single review by ID
 *
 * @OA\Get(
 *     path="/api/reviews/{id}",
 *     summary="Get a specific review",
 *     tags={"Reviews"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Review details",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="user_id", type="integer", example=3),
 *             @OA\Property(property="product_id", type="integer", example=5),
 *             @OA\Property(property="rating", type="integer", example=4),
 *             @OA\Property(property="comment", type="string", example="Great product!")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Review not found"
 *     )
 * )
 */
public function show($id)
{
    $review = Review::with(['user', 'product'])->find($id);

    if (!$review) {
        return response()->json(['message' => 'Review not found'], 404);
    }

    return response()->json($review);
}

    /**
     * @OA\Put(
     *     path="/api/reviews/{id}",
     *     summary="Update a review",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5),
     *             @OA\Property(property="comment", type="string", example="Updated comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Review not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review->update($validated);

        return response()->json(['message' => 'Review updated successfully', 'data' => $review]);
    }

    /**
     * @OA\Delete(
     *     path="/api/reviews/{id}",
     *     summary="Delete a review",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review deleted",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Review deleted"))
     *     ),
     *     @OA\Response(response=404, description="Review not found")
     * )
     */
    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted']);
    }
}
