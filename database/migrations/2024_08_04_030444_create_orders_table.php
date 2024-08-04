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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // Khóa chính
            $table->string('reciver'); // Người nhận
            $table->string('phone'); // Số điện thoại
            $table->string('address'); // Địa chỉ
            $table->decimal('total_money', 10, 2); // Tổng tiền
            $table->date('order_date'); // Ngày đặt hàng
            $table->string('order_status'); // Trạng thái đơn hàng
            $table->string('method_payment'); // Phương thức thanh toán
            $table->integer('total_quantity'); // Tổng số lượng
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Khóa ngoại liên kết với bảng users
            $table->timestamps(); // Các cột created_at và updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
