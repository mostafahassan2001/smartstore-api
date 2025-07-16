<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
   protected $fillable = [
    'name',
    'name_ar',
    'description',
    'description_ar',
    'discount_code',
    'discount_percentage',
    'start_date',
    'end_date',
    'is_active',
];

}
