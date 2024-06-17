<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarriosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('barrios_municipio')->insert(['name' => 'Centro Sur', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Centro Norte', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Parque Este', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Parque Oeste', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Libertador San Martin', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Zona Parque Industrial', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Aeroparque', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Daniel Saez', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'General Belgrano', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Pampa', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Peñi Trapum', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Brentana', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Progreso', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Union', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Ruca Quimey', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Cooperativa', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Pueblo Nuevo', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Nehuen Che', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Victor Ezio Zanni', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => '25 de Mayo', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Colonia 2 de Abril', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Colonia 21 de Septiembre', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Filli Dei Sur', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Monte Hermoso', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Filli Dei Norte', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Villa el Puestero', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Cono de seguridad', 'provincia_id' => 19]);
        DB::table('barrios_municipio')->insert(['name' => 'Ampliación Parque Industrial', 'provincia_id' => 19]);
    }
}
