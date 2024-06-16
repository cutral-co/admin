<?php

namespace App\Models\Traits;

use Spatie\Activitylog\LogOptions;

trait LogsActivity
{
    use \Spatie\Activitylog\Traits\LogsActivity;

    public function getDescriptionForEvent(string $eventName): string
    {
        $arr = ['updated' => 'actualizado', 'created' => 'creado', 'deleted' => 'eliminado'];
        $rta = $arr[$eventName];

        return "Este modelo ha sido {$rta}";
    }

    public function getActivitylogOptions(): LogOptions
    {
        $config = LogOptions::defaults();
        $config->logOnlyDirty();
        $config->logExcept(['created_at', 'updated_at']);
        $config->logOnly(['*']);

        return $config;
    }
}
