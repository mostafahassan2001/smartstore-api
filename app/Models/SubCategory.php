<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="SubCategory",
 *     type="object",
 *     title="SubCategory",
 *     required={"id", "name_en", "name_ar", "category_id"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name_en", type="string", example="Shirts"),
 *     @OA\Property(property="name_ar", type="string", example="قمصان"),
 *     @OA\Property(property="description_en", type="string", example="Subcategory for men's shirts"),
 *     @OA\Property(property="description_ar", type="string", example="قسم فرعي للقمصان الرجالية"),
 *     @OA\Property(property="category_id", type="integer", example=2),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(property="logo", type="string", example="SubCategory/logo.jpg")
 * )
 */
class SubCategory extends Model
{
    protected $fillable = [
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'logo',
        'category_id',
        'status',
    ];
}
