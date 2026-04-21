<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'de';

    protected $permission = 'domElectronico.sendMessage';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $this->addUserColumn();
        $this->addPermission();
        $this->create_domicilio();
        $this->create_origin();
        $this->create_notificacion();
        $this->create_tipo_destinatario();
        $this->create_domicilio_notificacion();
        $this->create_notificacion_archivo();
        $this->create_logs();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $this->create_logs(true);
        $this->create_notificacion_archivo(true);
        $this->create_domicilio_notificacion(true);
        $this->create_tipo_destinatario(true);
        $this->create_notificacion(true);
        $this->create_origin(true);
        $this->create_domicilio(true);
        $this->removePermission();
        $this->removeUserColumn();
    }

    private function addUserColumn() {
        if (!Schema::hasColumn('users', 'de_id')) {
            Schema::table('users', function ($table) {
                $table->unsignedBigInteger('de_id')->nullable()->after('cuit');
            });
        }
    }

    private function removeUserColumn() {
        if (Schema::hasColumn('users', 'de_id')) {
            Schema::table('users', function ($table) {
                $table->dropColumn('de_id');
            });
        }
    }

    private function addPermission() {
        $exists = DB::table('permissions')
            ->where('name', $this->permission)
            ->exists();

        if (!$exists) {
            DB::table('permissions')->insert([
                'name' => $this->permission,
                'guard_name' => 'web',
                'description' => 'Permite enviar notificaciones a domicilios electrónicos'
            ]);
        }
    }

    private function removePermission() {
        $permissionId = DB::table('permissions')
            ->where('name', $this->permission)
            ->value('id');

        if ($permissionId) {
            DB::table('model_has_permissions')
                ->where('permission_id', $permissionId)
                ->delete();

            DB::table('permissions')
                ->where('name', $this->permission)
                ->delete();
        }
    }

    private function create_domicilio($down = false) {
        if (!$down) {
            if (!Schema::hasTable('de_domicilio')) {
                Schema::create('de_domicilio', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id')->nullable();

                    $table->string('email');
                    $table->string('phone');
                    $table->string('domicilio_real');
                    $table->string('nombre');
                    $table->string('documento');
                    $table->string('renaper_id')->nullable();
                    $table->boolean('is_verified');
                    $table->string('token')->nullable();

                    $table->timestamps();

                    $table->unique('user_id');
                });
            }
        } else {
            Schema::dropIfExists('de_domicilio');
        }
    }

    private function create_origin($down = false) {
        if (!$down) {
            if (!Schema::hasTable('de_origin')) {
                Schema::create('de_origin', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->string('descripcion');
                    $table->string('token');
                });

                DB::connection('de')->table('de_origin')->insert([
                    ['name' => 'mi-cutral-digital', 'descripcion' => 'Mi CutraL Digital', 'token' => 'TOKEN_MI_MUNI_DIGITAL'],
                    ['name' => 'test', 'descripcion' => 'Test', 'token' => 'TOKEN_TEST'],
                    ['name' => 'sistema-multas', 'descripcion' => 'Monitoreo Vial Digital', 'token' => 'TOKEN_FOTO_MULTAS'],
                ]);
            }
        } else {
            Schema::dropIfExists('de_origin');
        }
    }

    private function create_notificacion($down = false) {
        if (!$down) {
            if (!Schema::hasTable('de_notificacion')) {
                Schema::create('de_notificacion', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('origin_id');
                    $table->string('title');
                    $table->text('body');
                    $table->boolean('block')->default(false);
                    $table->json('data')->nullable();
                    $table->softDeletes();

                    $table->timestamps();

                    /* Relaciones */
                    $table->foreign('origin_id')->references('id')->on('de_origin');
                });
            }
        } else {
            Schema::dropIfExists('de_notificacion');
        }
    }

    private function create_tipo_destinatario($down = false) {
        if (!$down) {
            if (!Schema::hasTable('de_tipo_destinatario')) {
                Schema::create('de_tipo_destinatario', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->string('descripcion');
                });

                DB::connection('de')->table('de_tipo_destinatario')->insert([
                    ['name' => 'destinatario', 'descripcion' => 'Persona afectada directamente a la notificacion'],
                    ['name' => 'representante', 'descripcion' => 'Copia carbon'],
                ]);
            }
        } else {
            Schema::dropIfExists('de_tipo_destinatario');
        }
    }

    private function create_domicilio_notificacion($down = false) {
        if (!$down) {
            if (!Schema::hasTable('de_domicilio_notificacion')) {
                Schema::create('de_domicilio_notificacion', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('domicilio_id');
                    $table->unsignedBigInteger('notificacion_id');
                    $table->unsignedBigInteger('tipo_destinatario_id');
                    $table->datetime('fecha_recibido');
                    $table->datetime('fecha_visto')->nullable();
                    $table->datetime('fecha_archivado')->nullable();

                    /* Relaciones */
                    $table->foreign('domicilio_id')->references('id')->on('de_domicilio');
                    $table->foreign('notificacion_id')->references('id')->on('de_notificacion');
                    $table->foreign('tipo_destinatario_id')->references('id')->on('de_tipo_destinatario');
                });
            }
        } else {
            Schema::dropIfExists('de_domicilio_notificacion');
        }
    }

    private function create_notificacion_archivo($down = false) {
        if (!$down) {
            if (!Schema::hasTable('de_notificacion_archivo')) {
                Schema::create('de_notificacion_archivo', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('notificacion_id');
                    $table->string('name');
                    $table->string('path');

                    /* Relaciones */
                    $table->foreign('notificacion_id')->references('id')->on('de_notificacion');
                });
            }
        } else {
            Schema::dropIfExists('de_notificacion_archivo');
        }
    }

    private function create_logs($down = false) {
        if (!$down) {
            if (!Schema::hasTable('de_logs')) {
                Schema::create('de_logs', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('domicilio_id')->nullable();
                    $table->unsignedBigInteger('notificacion_id')->nullable();
                    $table->string('tipo_destinatario')->nullable();
                    $table->string('message');
                    $table->json('attributes')->nullable();

                    $table->timestamps();

                    /* Relaciones */
                    $table->foreign('notificacion_id')->references('id')->on('de_notificacion');
                    $table->foreign('domicilio_id')->references('id')->on('de_domicilio');
                });
            }
        } else {
            Schema::dropIfExists('de_logs');
        }
    }
};
