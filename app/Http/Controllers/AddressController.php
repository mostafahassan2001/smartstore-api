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
     * Get paginated list of addresses
     * 
     * @OA\Get(
     *     path="/api/address",
     *     summary="Get paginated addresses",
     *     @OA\Parameter(name="pageNumber", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="pageSize", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Paginated address list"),
     * )
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $pageNumber = $request->input('pageNumber', 1);

        $addresses = Address::paginate($pageSize, ['*'], 'page', $pageNumber);

        return response()->json([
            'pageNumber' => $addresses->currentPage(),
            'pageSize'   => $addresses->perPage(),
            'total'      => $addresses->total(),
            'data'       => $addresses->items(),
        ]);
    }

    /**
     * Get a single address by ID
     * 
     * @OA\Get(
     *     path="/api/address/{id}",
     *     summary="Get a single address",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Address found"),
     *     @OA\Response(response=404, description="Address not found"),
     * )
     */
    public function show($id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        return response()->json($address);
    }

    /**
     * Store a new address
     * 
     * @OA\Post(
     *     path="/api/address",
     *     summary="Create a new address",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "city", "street", "building"},
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="city", type="string"),
     *             @OA\Property(property="street", type="string"),
     *             @OA\Property(property="building", type="string"),
     *             @OA\Property(property="is_default", type="boolean"),
     *         )
     *     ),
     *     @OA\Response(response=201, description="Address created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'city'        => 'required|string',
            'street'      => 'required|string',
            'building'    => 'required|string',
            'is_default'  => 'nullable|boolean',
        ]);

        if (!empty($validated['is_default'])) {
            Address::where('user_id', $validated['user_id'])->update(['is_default' => false]);
        }

        $address = Address::create($validated);

        return response()->json($address, 201);
    }

    /**
     * Update an existing address
     * 
     * @OA\Put(
     *     path="/api/address/{id}",
     *     summary="Update an address",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="city", type="string"),
     *             @OA\Property(property="street", type="string"),
     *             @OA\Property(property="building", type="string"),
     *             @OA\Property(property="is_default", type="boolean"),
     *         )
     *     ),
     *     @OA\Response(response=200, description="Address updated"),
     *     @OA\Response(response=404, description="Address not found"),
     * )
     */
    public function update(Request $request, $id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        $validated = $request->validate([
            'city'        => 'sometimes|string',
            'street'      => 'sometimes|string',
            'building'    => 'sometimes|string',
            'is_default'  => 'nullable|boolean',
        ]);

        if (!empty($validated['is_default'])) {
            Address::where('user_id', $address->user_id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json(['message' => 'Address updated']);
    }

    /**
     * Delete an address
     * 
     * @OA\Delete(
     *     path="/api/address/{id}",
     *     summary="Delete an address",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Address deleted"),
     *     @OA\Response(response=404, description="Address not found"),
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
     * Set an address as default
     * 
     * @OA\Post(
     *     path="/api/address/{id}/default",
     *     summary="Set address as default",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Default address set"),
     *     @OA\Response(response=404, description="Address not found"),
     * )
     */
    public function setDefault($id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        Address::where('user_id', $address->user_id)->update(['is_default' => false]);

        $address->is_default = true;
        $address->save();

        return response()->json(['message' => 'Default address set successfully']);
    }
}
