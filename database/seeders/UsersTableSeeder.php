<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
public function run()
{
    User::create([
        'firstname' => 'Admin',
        'lastname' => 'User',
        'email' => 'admin@test.com',
        'email_verified_at' => now(),
        'password' => Hash::make('admin@123'), // أو أي باسورد تريده
        'role' => 'admin',
            'email_verified_at' => now(),
        'remember_token' => Str::random(10),
    ]);
}
}
