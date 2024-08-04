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
        Schema::create('carts', function (Blueprint $table) {
            $table->id(); // Khóa chính
            $table->decimal('total_price', 10, 2); // Tổng giá, với định dạng số thập phân
            $table->integer('total_quantity'); // Tổng số lượng
            $table->foreignId('user_id'); // Khóa ngoại liên kết với bảng users
            $table->timestamps(); // Các cột created_at và updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
