<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Contoh: Mengubah nilai kolom pada data yang sudah ada
        DB::table('users')->where('provinces_id', 'integer')->update(['provinces' => 'string']);
        DB::table('users')->where('regencies_id', 'integer')->update(['regencies' => 'string']);

        // Contoh: Menambahkan data baru
        DB::table('stores')->insert([
            'logo' => 'string',
            // ...
        ]);
    }
}
