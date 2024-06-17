<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('provincias')->insert(['name' => 'Ciudad Autónoma de Buenos Aires (CABA)']);
        DB::table('provincias')->insert(['name' => 'Buenos Aires']);
        DB::table('provincias')->insert(['name' => 'Catamarca']);
        DB::table('provincias')->insert(['name' => 'Córdoba']);
        DB::table('provincias')->insert(['name' => 'Corrientes']);
        DB::table('provincias')->insert(['name' => 'Entre Ríos']);
        DB::table('provincias')->insert(['name' => 'Jujuy']);
        DB::table('provincias')->insert(['name' => 'Mendoza']);
        DB::table('provincias')->insert(['name' => 'La Rioja']);
        DB::table('provincias')->insert(['name' => 'Salta']);
        DB::table('provincias')->insert(['name' => 'San Juan']);
        DB::table('provincias')->insert(['name' => 'San Luis']);
        DB::table('provincias')->insert(['name' => 'Santa Fe']);
        DB::table('provincias')->insert(['name' => 'Santiago del Estero']);
        DB::table('provincias')->insert(['name' => 'Tucumán']);
        DB::table('provincias')->insert(['name' => 'Chaco']);
        DB::table('provincias')->insert(['name' => 'Chubut']);
        DB::table('provincias')->insert(['name' => 'Formosa']);
        DB::table('provincias')->insert(['name' => 'Misiones']);
        DB::table('provincias')->insert(['name' => 'Neuquén']);
        DB::table('provincias')->insert(['name' => 'La Pampa']);
        DB::table('provincias')->insert(['name' => 'Río Negro']);
        DB::table('provincias')->insert(['name' => 'Santa Cruz']);
        DB::table('provincias')->insert(['name' => 'Tierra del Fuego']);
    }
}
