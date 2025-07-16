<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
/**
 * @OA\Schema(
 *     schema="Address",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=5),
 *     @OA\Property(property="country", type="string", example="Egypt"),
 *     @OA\Property(property="city", type="string", example="Cairo"),
 *     @OA\Property(property="street", type="string", example="El Tahrir"),
 *     @OA\Property(property="building_number", type="string", example="12"),
 *     @OA\Property(property="postal_code", type="string", example="12345")
 * )
 */

class AddressController extends Controller
{
    /**
     * Get all addresses
     *
     * @OA\Get(
     *     path="/api/addresses",
     *     summary="Get all addresses",
     *     tags={"Address"},
     *     @OA\Response(
     *         response=200,
     *         description="List of addresses",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Address"))
     *     )
     * )
     */
    public function index()
    {
        return Address::all();
    }

    /**
     * Store new address
     *
     * @OA\Post(
     *     path="/api/addresses",
     *     summary="Create new address",
     *     tags={"Address"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "city", "street", "building"},
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="city", type="string"),
     *             @OA\Property(property="street", type="string"),
     *             @OA\Property(property="building", type="string"),
     *             @OA\Property(property="is_default", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Address created",
     *         @OA\JsonContent(ref="#/components/schemas/Address")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'city' => 'required|string',
            'street' => 'required|string',
            'building' => 'required|string',
            'is_default' => 'nullable|boolean',
        ]);

        if (isset($validated['is_default']) && $validated['is_default']) {
            Address::where('user_id', $validated['user_id'])->update(['is_default' => false]);
        }

        $address = Address::create($validated);

        return response()->json($address, 201);
    }
/**
 * Display a specific address
 *
 * @OA\Get(
 *     path="/api/address/{id}",
 *     summary="Get address by ID",
 *     tags={"Address"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Address details",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="user_id", type="integer"),
 *             @OA\Property(property="city", type="string"),
 *             @OA\Property(property="region", type="string"),
 *             @OA\Property(property="details", type="string"),
 *             @OA\Property(property="is_default", type="boolean"),
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Address not found"
 *     )
 * )
 */
public function show($id)
{
    $address = \App\Models\Address::find($id);

    if (!$address) {
        return response()->json(['message' => 'Address not found'], 404);
    }

    return response()->json($address);
}

    /**
     * Update address
     *
     * @OA\Put(
     *     path="/api/addresses/{id}",
     *     summary="Update address",
     *     tags={"Address"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="city", type="string"),
     *             @OA\Property(property="street", type="string"),
     *             @OA\Property(property="building", type="string"),
     *             @OA\Property(property="is_default", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Address updated")
     * )
     */
    public function update(Request $request, $id)
    {
        $address = Address::find($id);
        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        $validated = $request->validate([
            'city' => 'sometimes|string',
            'street' => 'sometimes|string',
            'building' => 'sometimes|string',
            'is_default' => 'nullable|boolean',
        ]);

        if (isset($validated['is_default']) && $validated['is_default']) {
            Address::where('user_id', $address->user_id)->update(['is_default' => false]);
        }

        $address->update($validated);
        return response()->json(['message' => 'Address updated']);
    }

    /**
     * Delete address
     *
     * @OA\Delete(
     *     path="/api/addresses/{id}",
     *     summary="Delete address",
     *     tags={"Address"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Address deleted")
     * )
     */
    public function destroy($id)
    {
        $address = Address::find($id);
        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        $address->delete();
        return response()->json(['message' => 'Address deleted']);
    }

    /**
     * Set address as default
     *
     * @OA\Put(
     *     path="/api/address/{id}/set-default",
     *     summary="Set an address as default for the user",
     *     tags={"Address"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Address ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Default address set successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Default address set successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Address not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Address not found")
     *         )
     *     )
     * )
     */
    public function setDefault($id)
    {
        $address = Address::find($id);
        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        Address::where('user_id', $address->user_id)
            ->update(['is_default' => false]);

        $address->is_default = true;
        $address->save();

        return response()->json(['message' => 'Default address set successfully']);
    }
}
