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
        Schema::create('product_details', function (Blueprint $table) {
            $table->id();
            $table->text('product_detail_intro');
            $table->text('product_detail_desc');
            $table->decimal('product_detail_weight', 8, 2);
            $table->date('product_detail_mfg'); // Manufacturing date
            $table->date('product_detail_exp'); // Expiration date
            $table->string('product_detail_origin');
            $table->text('product_detail_manual');
            $table->foreignId('product_id'); // Foreign key
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_details');
    }
};
