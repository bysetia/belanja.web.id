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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('logo')->nullable();
            $table->longText('description')->nullable();
            $table->longText('address_one')->nullable();
            $table->longText('address_two')->nullable();
            $table->string('provinces')->nullable();
            $table->string('regencies')->nullable();
            $table->integer('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->integer('status')->nullable();
            $table->unsignedBigInteger('user_id'); // menambahkan kolom user_id untuk relasi
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
