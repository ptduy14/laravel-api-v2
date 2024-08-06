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
        Schema::table('orders', function (Blueprint $table) {
            // Change the type of order_status from string to integer with a default value of 0
            $table->integer('order_status')->default(0)->change();

            // Add new column order_status_desc with type string
            $table->string('order_status_desc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Change the type of order_status back to string
            $table->string('order_status')->change();

            // Drop the order_status_desc column
            $table->dropColumn('order_status_desc');
        });
    }
};
