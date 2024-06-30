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
        Schema::create('persons', function (Blueprint $table) {
            $table->id();
            $table->string('cuit')->unique();
            $table->string('name');
            $table->string('lastname')->nullable();
            $table->boolean('is_company')->default(0);

            /* Datos de contacto */
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();

            /* Direaccion Micro */
            $table->string('calle')->nullable();
            $table->string('altura')->nullable();
            $table->string('manzana')->nullable();
            $table->string('lote')->nullable();
            $table->string('piso')->nullable();
            $table->string('depto')->nullable();

            /* Direccion Macro */
            $table->unsignedBigInteger('barrio_id')->nullable();
            $table->string('municipio')->nullable();
            $table->string('barrio')->nullable();
            $table->unsignedBigInteger('provincia_id')->nullable();

            /* Relaciones */
            $table->foreign('barrio_id')->references('id')->on('barrios_municipio');
            $table->foreign('provincia_id')->references('id')->on('provincias');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};
