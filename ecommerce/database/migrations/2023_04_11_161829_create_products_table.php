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
        Schema::create('products', function (Blueprint $table) {
            // ? menambahkan column
            $table->id();
            $table->bigInteger('user_id');
            $table->string('name');
            $table->float('price');
            $table->longText('description');
            $table->integer('quantity');
            $table->double('rate')->nullable();
            $table->text('picturePath')->nullable();
            $table->string('product_origin')->nullable();
            $table->string('product_material')->nullable();
            $table->double('weight')->nullable();

            // ? relasi category
            // Add foreign key constraint for the categories column
            $table->foreignId('category_id')->constrained('product_categories');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
