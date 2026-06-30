<?php

namespace App\Services\Legal;

use Illuminate\Support\Facades\File;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LegalContentService
{
    private const FORMAT_MARKDOWN = 'markdown';

    /**
     * @return array<string, mixed>
     */
    public function getByKey(string $key, bool $includeMeta = true): array
    {
        $documents = $this->documents();

        if (!array_key_exists($key, $documents)) {
            throw new NotFoundHttpException("No existe contenido legal para la clave {$key}");
        }

        $document = $documents[$key];
        $path = resource_path($document['path']);

        if (!File::exists($path)) {
            throw new NotFoundHttpException("No existe el archivo asociado a la clave {$key}");
        }

        $content = File::get($path);

        if (!$includeMeta) {
            return [
                'content' => $content,
            ];
        }

        return [
            'key' => $key,
            'title' => $document['title'],
            'version' => $document['version'],
            'last_updated' => $document['last_updated'],
            'format' => self::FORMAT_MARKDOWN,
            'content' => $content,
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function documents(): array
    {
        return [
            'user-registration.terms-and-conditions' => [
                'title' => 'Terminos y condiciones de registro',
                'version' => '1.0.0',
                'last_updated' => '2026-06-30',
                'path' => 'legal/terms-and-conditions/user-registration.md',
            ],
        ];
    }
}
