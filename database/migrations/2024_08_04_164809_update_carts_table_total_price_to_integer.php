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
        Schema::table('carts', function (Blueprint $table) {
            $table->integer('total_price')->change(); // Thay đổi kiểu dữ liệu cột 'total_price' thành integer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->decimal('total_price', 8, 2)->change(); // Khôi phục kiểu dữ liệu cột 'total_price' về decimal nếu cần
        });
    }
};
