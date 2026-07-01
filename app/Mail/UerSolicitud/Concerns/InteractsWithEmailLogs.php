<?php

namespace App\Mail\UerSolicitud\Concerns;

trait InteractsWithEmailLogs
{
    protected function maskToken(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $length = strlen($value);

        if ($length <= 8) {
            return $value;
        }

        return substr($value, 0, 4) . '...' . substr($value, -4);
    }
}
