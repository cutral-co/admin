<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('persons', 'barrio') && !Schema::hasColumn('persons', 'otro_barrio')) {
            DB::statement('ALTER TABLE persons CHANGE barrio otro_barrio VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('persons', 'otro_barrio') && !Schema::hasColumn('persons', 'barrio')) {
            DB::statement('ALTER TABLE persons CHANGE otro_barrio barrio VARCHAR(255) NULL');
        }
    }
};
