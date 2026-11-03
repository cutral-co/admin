<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private const APP = 'tramites-patentes';
    private const PERMISSION = 'admin.tramites-patentes';

    public function up(): void
    {
        $this->registerApp();
        $this->registerPermission();

        Schema::create('tram_pat_tipos_tramites', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('tram_pat_requisitos_documentacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_tramite_id')->constrained('tram_pat_tipos_tramites')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->json('formatos_permitidos');
            $table->boolean('requerido')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['tipo_tramite_id', 'activo']);
        });

        Schema::create('tram_pat_tramites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('tipo_tramite_id')->constrained('tram_pat_tipos_tramites')->restrictOnDelete();
            $table->string('dominio', 20)->nullable();
            $table->string('email_contacto', 150);
            $table->string('estado', 50)->default('pendiente');
            $table->text('observacion_final')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['estado', 'created_at']);
        });

        Schema::create('tram_pat_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tramite_id')->constrained('tram_pat_tramites')->cascadeOnDelete();
            $table->foreignId('requisito_documentacion_id')
                ->nullable()
                ->constrained('tram_pat_requisitos_documentacion')
                ->nullOnDelete();
            $table->string('titulo');
            $table->string('ruta')->nullable();
            $table->string('nombre_original')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('estado', 50)->default('pendiente');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index(['tramite_id', 'estado']);
        });

        Schema::create('tram_pat_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tramite_id')->constrained('tram_pat_tramites')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('accion', 50);
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index(['tramite_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tram_pat_movimientos');
        Schema::dropIfExists('tram_pat_documentos');
        Schema::dropIfExists('tram_pat_tramites');
        Schema::dropIfExists('tram_pat_requisitos_documentacion');
        Schema::dropIfExists('tram_pat_tipos_tramites');

        $this->unregisterPermission();
        DB::table('apps')->where('name', self::APP)->delete();
    }

    private function registerApp(): void
    {
        DB::table('apps')->updateOrInsert(
            ['name' => self::APP],
            [
                'title' => 'Tramites de patentes',
                'url' => '/tramites-patentes',
                'description' => 'Sistema de gestión de trámites de registro de vehículos.',
                'keywords' => 'patentes vehiculos rodados alta baja transferencia titularidad',
                'enabled' => true,
                'image' => null,
                'required_permission' => false,
                'is_internal' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    private function registerPermission(): void
    {
        $permission = Permission::query()->firstOrCreate(
            ['name' => self::PERMISSION, 'guard_name' => 'web'],
            ['description' => 'Gestionar trámites de patentes'],
        );

        $sudoRole = Role::query()->where('name', 'sudo')->first();
        if ($sudoRole && !$sudoRole->hasPermissionTo($permission)) {
            $sudoRole->givePermissionTo($permission);
        }
    }

    private function unregisterPermission(): void
    {
        $permission = Permission::query()
            ->where('name', self::PERMISSION)
            ->where('guard_name', 'web')
            ->first();

        if (!$permission) {
            return;
        }

        $permission->roles()->detach();
        $permission->delete();
    }
};
