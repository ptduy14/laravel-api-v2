<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cart_product', function (Blueprint $table) {
            $table->id(); // Khóa chính của bảng trung gian
            $table->foreignId('cart_id'); // Khóa ngoại liên kết với bảng carts
            $table->foreignId('product_id'); // Khóa ngoại liên kết với bảng products
            $table->integer('quantity'); // Trường lưu trữ số lượng sản phẩm trong cart
            $table->timestamps(); // Các cột created_at và updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_product');
    }
};
