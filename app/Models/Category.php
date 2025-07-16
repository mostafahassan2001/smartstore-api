<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
  protected $fillable = [
    'name_en',
    'name_ar',
    'description_en',
    'description_ar',
    'logo',
    'status',
];

}
