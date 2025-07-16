<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'country',
        'city',
        'street',
        'zip_code',
    ];

    // علاقة العنوان بمستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
