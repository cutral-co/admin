<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private const ADMIN_APP = 'adm-turnero-licencia-conducir';
    private const PUBLIC_APP = 'turnero-licencia-conducir';
    private const PERMISSION = 'app.enter.adm-turnero-licencia-conducir';

    public function up(): void
    {
        $this->createSolicitudesTable();
        $this->registerApps();
        $this->registerPermission();
    }

    public function down(): void
    {
        $this->unregisterPermission();
        $this->unregisterApps();
        Schema::dropIfExists('turnero_lc_solicitudes');
    }

    private function createSolicitudesTable(): void
    {
        Schema::create('turnero_lc_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 8);
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('telefono', 50);
            $table->string('email', 150);
            $table->string('estado', 50);
            $table->timestamps();

            $table->index('estado');
            $table->index('created_at');
            $table->index(['dni', 'estado']);
        });
    }

    private function registerApps(): void
    {
        DB::table('apps')->updateOrInsert(
            ['name' => self::PUBLIC_APP],
            [
                'title' => 'Turnero Licencia de Conducir',
                'url' => 'http://admin-client.test/#/turnero-licencia-conducir',
                'description' => 'Solicitud pública de turnos para licencia de conducir',
                'keywords' => 'turnero licencia conducir turno',
                'enabled' => 1,
                'image' => null,
                'required_permission' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        DB::table('apps')->updateOrInsert(
            ['name' => self::ADMIN_APP],
            [
                'title' => 'Turnero Licencia de Conducir Admin',
                'url' => 'http://admin-client.test/#/admin/turnero-licencia-conducir',
                'description' => 'Gestión de solicitudes de turnos para licencia de conducir',
                'keywords' => 'turnero licencia conducir admin backoffice',
                'enabled' => 1,
                'image' => null,
                'required_permission' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    private function unregisterApps(): void
    {
        DB::table('apps')
            ->whereIn('name', [self::PUBLIC_APP, self::ADMIN_APP])
            ->delete();
    }

    private function registerPermission(): void
    {
        $permission = Permission::query()->firstOrCreate(
            ['name' => self::PERMISSION, 'guard_name' => 'web'],
            ['description' => 'Ingresar a Turnero Licencia de Conducir Admin'],
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

        $roles = Role::query()->get();
        foreach ($roles as $role) {
            if ($role->hasPermissionTo($permission)) {
                $role->revokePermissionTo($permission);
            }
        }

        $permission->delete();
    }
};
