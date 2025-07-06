<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
$user = User::create([
            'role' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'), // كلمة مرور مشفرة
            'firstAndLastName' => 'Admin User',
            'fatherName' => 'AdminFather',
            'phoneNumber' => '0999999999',
            'birthDate' => '1990-01-01',
            'address' => 'Damas',
            'remember_token' => Str::random(10),
        ]);

        // إنشاء سجل مرتبط في جدول admins
        Admin::create([
            'userID' => $user->id,
        ]);    }
}
