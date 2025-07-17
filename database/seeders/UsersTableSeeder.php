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
    \App\Models\User::create([
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => 'admin@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin@123'),
            'remember_token' => Str::random(10), //
    ]);
}
}
