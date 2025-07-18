<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject; // ✅ أضف هذا السطر

class User extends Authenticatable implements JWTSubject // ✅ نفذ JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getNameAttribute(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    // ✅ هاتين الدالتين مطلوبتان لـ JWTAuth:
    public function getJWTIdentifier()
    {
        return $this->getKey(); // عادة الـ id
    }

    public function getJWTCustomClaims()
    {
        return []; // أي Claims إضافية تقدر تضيفها هنا
    }
}
