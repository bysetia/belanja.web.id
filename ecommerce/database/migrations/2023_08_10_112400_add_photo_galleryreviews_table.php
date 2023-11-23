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
         Schema::table('galleryreviews', function (Blueprint $table) {
            $table->string('image_path_2')->nullable();
            $table->string('image_path_3')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('galleryreviews', function (Blueprint $table) {
            $table->dropColumn(['image_path_2', 'image_path_3']);
        });
    }
};
