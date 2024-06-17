<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Hash};

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'cuit' => '20999999991',
            'password' => Hash::make('20999999991'),
        ]);

        $this->call([
            ProvinciasSeeder::class,
            BarriosSeeder::class,
            PermissionsDemoSeeder::class,
        ]);
    }
}
