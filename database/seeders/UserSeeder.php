<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo 10 bản ghi mẫu
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => 'User ' . $i,
                'email' => 'user' . $i . '@gmail.com',
                'phone' => '012345678' . $i, // Giả sử số điện thoại có độ dài tối đa là 11 ký tự
                'address' => 'Address ' . $i,
                'gender' => $i % 2, // 0 hoặc 1, giả định giới tính
                'verify' => $i % 2, // 0 hoặc 1, giả định trạng thái xác thực
                'email_verified_at' => now(),
                'password' => Hash::make('password'), // Mật khẩu đã mã hóa
                'remember_token' => Str::random(10), // Token nhớ đăng nhập
            ]);

            // Gán vai trò cho người dùng
            $user->assignRole('user'); // Gán vai trò 'user' cho tất cả người dùng
        }
    }
}
