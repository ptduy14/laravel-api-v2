<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'phone' => '012345678', // Giả sử số điện thoại có độ dài tối đa là 11 ký tự
            'address' => 'Address',
            'gender' => 1, // 0 hoặc 1, giả định giới tính
            'verify' => 1, // 0 hoặc 1, giả định trạng thái xác thực
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Mật khẩu đã mã hóa
            'remember_token' => Str::random(10), // Token nhớ đăng nhập
        ]);

        $admin->assignRole('admin');
    }
}
