<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersSolicitudesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->string('lastname');
            $table->string('name');
            $table->string('cuit');
            $table->string('email');
            $table->string('phone');
            $table->unsignedBigInteger('barrio_id');
            $table->string('municipio')->nullable();
            $table->string('otro_barrio')->nullable();
            $table->string('calle');
            $table->string('altura');
            $table->string('manzana')->nullable();
            $table->string('lote')->nullable();
            $table->string('piso')->nullable();
            $table->string('depto')->nullable();
            $table->string('token_verificacion')->nullable();
            $table->timestamp('ultimo_envio_email')->nullable();
            $table->timestamp('fecha_verificado')->nullable();
            $table->unsignedBigInteger('estado_id')->default(1);

            $table->timestamps();

            /* relaciones */
            $table->foreign('barrio_id')->references('id')->on('barrios_municipio');
            $table->foreign('estado_id')->references('id')->on('tables');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('solicitudes');
    }
}
