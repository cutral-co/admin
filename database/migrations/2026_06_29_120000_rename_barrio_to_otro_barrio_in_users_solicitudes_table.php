<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameBarrioToOtroBarrioInUsersSolicitudesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            'ALTER TABLE users_solicitudes CHANGE barrio otro_barrio VARCHAR(255) NULL'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement(
            'ALTER TABLE users_solicitudes CHANGE otro_barrio barrio VARCHAR(255) NULL'
        );
    }
}
