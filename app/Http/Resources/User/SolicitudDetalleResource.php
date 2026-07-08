<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SolicitudDetalleResource extends JsonResource
{
    public function toArray($request)
    {
        $array = parent::toArray($request);

        unset(
            $array['files'],
            $array['barrio_id'],
            $array['otro_barrio'],
            $array['provincia_id']
        );

        return [
            ...$array,
            ...$this->resource->domicilioFisico(),
            'estado' => $this->estado?->value,
            'document_front' => $this->formatFileByKey('document_front'),
            'document_back' => $this->formatFileByKey('document_back'),
        ];
    }

    private function formatFileByKey(string $key): ?array
    {
        $file = $this->resource->files?->firstWhere('key', $key);

        if (!$file) {
            return null;
        }

        return [
            'id' => $file->id,
            'original_name' => $file->original_name,
            'extension' => $file->extension,
            'mime_type' => $file->mime_type,
            'size' => $file->size,
            'base64' => $this->fileToDataUri($file->disk, $file->path, $file->mime_type),
        ];
    }

    private function fileToDataUri(string $disk, string $path, ?string $mimeType): ?string
    {
        if (!Storage::disk($disk)->exists($path)) {
            return null;
        }

        $content = Storage::disk($disk)->get($path);
        $safeMimeType = $mimeType ?: 'application/octet-stream';

        return 'data:' . $safeMimeType . ';base64,' . base64_encode($content);
    }
}
