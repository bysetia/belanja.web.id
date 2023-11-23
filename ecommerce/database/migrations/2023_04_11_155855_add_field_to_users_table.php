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
        Schema::table('users', function (Blueprint $table) {
            // ? menambahkan role setelah email dan default = user
            $table->string('roles')->after('email')->default('USER'); 
            // ? menambahkan phone setelah roles nullable
            $table->string('phone')->after('roles')->nullable();
            // ? menambahkan username setelah phone nullable
            $table->string('username')->after('phone')->nullable();
            $table->longText('address_one')->after('username')->nullable();
            $table->longText('address_two')->after('address_one')->nullable();
            $table->string('provinces')->after('address_two')->nullable();
            $table->string('regencies')->after('provinces')->nullable();
            $table->integer('zip_code')->after('regencies')->nullable();
            $table->string('country')->after('zip_code')->nullable();
            // $table->string('store_name')->after('phone')->nullable();
            // $table->bigInteger('categories_id')->after('email')->nullable();
            // $table->integer('store_status')->after('email')->nullable();
            // $table->longText('store_description')->after('email')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('roles');
            $table->dropColumn('phone');
            $table->dropColumn('username');
            $table->dropColumn('address_one');
            $table->dropColumn('address_two');
            $table->dropColumn('provinces_id');
            $table->dropColumn('regencies_id');
            $table->dropColumn('zip_code');
            $table->dropColumn('country');
            // $table->dropColumn('store_name');
            // $table->dropColumn('categories_id');
            // $table->dropColumn('store_Status');

        });
    }
};
