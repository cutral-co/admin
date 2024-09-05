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
        Schema::create('user_pago_online', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('opid')->unique();
            $table->string('nro_comprobante')->unique();
            $table->json('comprobante');
<<<<<<< HEAD
            $table->string('mp_preference_id')->unique()->nullable();
            $table->json('mp_preference')->nullable();
=======
            $table->json('mp_preference');
>>>>>>> e89a489f0651fc13f8ba51320030dd6f1b2e6220
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_pago_online');
    }
};