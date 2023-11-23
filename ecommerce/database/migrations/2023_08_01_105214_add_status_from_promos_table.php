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
        Schema::table('promos', function (Blueprint $table) {
             $table->string('status')->default('active');
        });
        // Mendapatkan tanggal saat ini dalam format 'Y-m-d'
        $today = date('Y-m-d');

        // Mengubah status promosi menjadi 'inactive' jika tanggal awalnya kurang dari tanggal saat ini
        DB::statement("UPDATE promos SET status = 'inactive' WHERE start_date < '{$today}'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            //
        });
    }
};
