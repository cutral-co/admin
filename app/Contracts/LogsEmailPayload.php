<?php

namespace App\Contracts;

interface LogsEmailPayload
{
    public function getEmailTemplateKey(): string;

    /**
     * @return array<string, mixed>
     */
    public function getEmailLogPayload(): array;

    /**
     * @return array<string, mixed>
     */
    public function getEmailLogMeta(): array;
}
