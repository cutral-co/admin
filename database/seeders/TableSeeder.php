<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insertData = [];
        $insertData = $this->estados_users_solicitudes($insertData);
        DB::table('tables')->insert($insertData);
    }
    private function estados_users_solicitudes($insertData)
    {
        $values = [
            "Nuevo",
            "Aprobado",
            "Rechazado",
        ];
        return $this->prepareData($values, $insertData, 'users_solicitudes.estados');
    }

    private function prepareData($values, $insertData, $name, $keyValue = false)
    {
        foreach ($values as $key => $value) {
            $snakeCaseValue = Str::snake(
                preg_replace('/[^a-z0-9_]/', '', Str::ascii(strtolower($value)))
            );
            $insertData[] = [
                'name' => $name,
                'value' =>  $keyValue ? $key : $snakeCaseValue,
                'label' => $value,
                //'descripcion' => $value,
            ];
        }

        return $insertData;
    }
}
