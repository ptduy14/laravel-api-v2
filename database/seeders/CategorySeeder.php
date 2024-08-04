<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo các bản ghi mẫu
        $categories = [
            ['category_name' => 'Laptop', 'category_desc' => 'Máy tính xách tay', 'category_status' => 1],
            ['category_name' => 'Bàn phím', 'category_desc' => 'Phụ kiện bàn phím', 'category_status' => 1],
            ['category_name' => 'Chuột', 'category_desc' => 'Chuột máy tính', 'category_status' => 1],
            ['category_name' => 'Tay nghe', 'category_desc' => 'Tai nghe âm thanh', 'category_status' => 1],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
