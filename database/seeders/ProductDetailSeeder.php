<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductDetail;
use Carbon\Carbon;

class ProductDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productDetails = [];
        for ($i = 1; $i <= 20; $i++) {
            $productDetails[] = [
                'product_detail_intro' => 'Intro for Product ' . $i,
                'product_detail_desc' => 'Description for Product ' . $i,
                'product_detail_weight' => 1.0 * $i, // Example weight, change as needed
                'product_detail_mfg' => Carbon::now()->subMonths($i)->format('Y-m-d'),
                'product_detail_exp' => Carbon::now()->addYears(1)->format('Y-m-d'),
                'product_detail_origin' => 'Origin ' . $i,
                'product_detail_manual' => 'http://example.com/manual' . $i,
                'product_id' => $i,
            ];
        }
        foreach ($productDetails as $productDetail) {
            productDetail::create($productDetail);
        }
    }
}
