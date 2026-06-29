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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('value');
            $table->string('label');
            //$table->string('descripcion');
            $table->boolean('activo')->nullable()->default(true);
            //$table->timestamps();
            $table->unique(['name', 'value']);
        });

        Schema::create('table_config', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->unsignedBigInteger('table_id');
            $table->string('classname')->nullable();
            $table->json('json')->nullable();

            //$table->timestamps();

            $table->unique(['name', 'table_id']);

            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_config');
        Schema::dropIfExists('tables');
    }
};
