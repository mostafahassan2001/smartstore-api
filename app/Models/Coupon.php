<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
    'code',
    'description',
    'discount_type',
    'discount_value',
    'max_uses',
    'used_count',
    'start_date',
    'end_date',
    'is_active',
];

}
