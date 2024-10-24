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
        Schema::connection('evento202411')->create('registros', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('lastname');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('dni');

            $table->string('opid')->nullable()->unique();
            $table->string('nro_comprobante')->nullable()->unique();
            $table->string('hash')->nullable()->unique();

            $table->json('comprobante')->nullable();
            $table->string('mp_preference_id')->nullable();
            $table->json('mp_preference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('evento202411')->dropIfExists('registros');
    }
};
