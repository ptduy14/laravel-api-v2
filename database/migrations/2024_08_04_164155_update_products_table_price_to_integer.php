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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('price')->change(); // Thay đổi kiểu dữ liệu cột 'price' thành integer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->change(); // Khôi phục kiểu dữ liệu cột 'price' về decimal nếu cần
        });
    }
};
