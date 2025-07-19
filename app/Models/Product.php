<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     required={"id", "name_en", "name_ar", "price"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name_en", type="string", example="Shirt"),
 *     @OA\Property(property="name_ar", type="string", example="قميص"),
 *     @OA\Property(property="description_en", type="string", example="Cotton shirt"),
 *     @OA\Property(property="description_ar", type="string", example="قميص قطن"),
 *     @OA\Property(property="price", type="number", format="float", example=99.99),
 *     @OA\Property(property="stock_quantity", type="number", format="integer", example=5),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(property="category_id", type="integer", example=2),
 *     @OA\Property(property="brand_id", type="integer", example=3),
 *     @OA\Property(property="colors", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="sizes", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="image", type="string", example="products/image.jpg")
 * )
 */

class Product extends Model
{
   protected $fillable = [
    'name_en',
    'name_ar',
    'description_en',
    'description_ar',
    'image',
    'colors',
    'sizes',
    'price',
    'status',
    'category_id',
    'subcategory_id',
    'brand_id',
];

protected $casts = [
    'colors' => 'array',
    'sizes' => 'array',
    'status' => 'boolean',
];

}
