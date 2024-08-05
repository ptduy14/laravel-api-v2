<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy tất cả các category
        $categories = Category::all();

        // Tạo 20 bản ghi sản phẩm mẫu
        $products = [];

        for ($i = 1; $i <= 20; $i++) {
            $products[] = [
                'product_name' => 'Product ' . $i,
                'product_price' => rand(1000, 5000), // Giá ngẫu nhiên từ 1000 đến 5000
                'product_status' => rand(0, 1), // Trạng thái ngẫu nhiên 0 hoặc 1
                'category_id' => $categories->random()->id, // Chọn ngẫu nhiên một category_id
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
