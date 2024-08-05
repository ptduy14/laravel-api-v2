<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('total_money')->change(); // Thay đổi kiểu dữ liệu cột 'total_money' thành integer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_money', 8, 2)->change(); // Khôi phục kiểu dữ liệu cột 'total_money' về decimal nếu cần
        });
    }
};
