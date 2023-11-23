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
        Schema::table('transaction_items', function (Blueprint $table) {
        // Hapus kolom transaction_id
        $table->dropColumn('transaction_id');

        // Tambahkan kolom cart_id dan definisi foreign key
        $table->unsignedBigInteger('cart_id');
        $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
