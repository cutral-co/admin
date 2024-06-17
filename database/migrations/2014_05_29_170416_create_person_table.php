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

            $table->string('email');
            $table->string('phone');
            $table->string('calle');
            $table->string('altura');
            $table->string('manzana')->nullable();
            $table->string('lote')->nullable();
            $table->string('piso')->nullable();
            $table->string('depto')->nullable();

            $table->unsignedBigInteger('barrio_id')->nullable();
            $table->string('municipio')->nullable();
            $table->string('barrio')->nullable();
            $table->unsignedBigInteger('provincia_id')->nullable();

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
