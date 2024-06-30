<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('title');
            $table->string('url');
            $table->string('description')->nullable();
            $table->string('keywords')->default('tramite de busqueda');
            $table->boolean('enabled')->default(1);
            $table->string('image')->nullable();
            $table->boolean('required_permission')->default(0);
            $table->timestamps();
        });

        DB::table('apps')->insert([
            'name' => 'ddjj-tasa-vial',
            'title' => 'Declaración Jurada Tasa Vial',
            'url' => 'http://admin-client.test/apps/ddjj-tasa-vial/',
            'required_permission' => true
        ]);

        DB::table('apps')->insert([
            'name' => 'template',
            'title' => 'TEMPLATE',
            'url' => 'http://admin-client.test/apps/template/',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apps');
    }
};
