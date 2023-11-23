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
        Schema::create('select_courier', function (Blueprint $table) {
             $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('courier_id');
            $table->timestamps();
    
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('courier_id')->references('id')->on('couriers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('select_courier');
    }
};
