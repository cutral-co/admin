<?php

namespace App\Models\Table;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $table = 'tables';

    protected $appends = ['styles'];

    protected $fillable = [
        'name',
        'value',
        'label',
        'descripcion',
        'activo',
    ];

    protected $hidden = [
        'descripcion',
        'activo',
        'pivot',
        'configs',
        'created_at',
        'updated_at',
    ];

    public function configs()
    {
        return $this->hasMany(TableConfig::class, 'table_id', 'id');
    }

    public function getStylesAttribute()
    {
        $styles = $this->configs->where('name', 'styles')->first();

        if (!$styles) {
            return null;
        }

        // Decodificar el campo `json`
        $decodedJson = json_decode($styles->json, true);

        // Verificar si el JSON tiene los valores esperados
        if (is_array($decodedJson) && isset($decodedJson['color'], $decodedJson['background'])) {
            return [
                'color' => $decodedJson['color'],
                'background' => $decodedJson['background'],
            ];
        }

        return null;
    }
}
