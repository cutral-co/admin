<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $table = 'files';

    protected $fillable = [
        'fileable_type',
        'fileable_id',
        'key',
        'disk',
        'name',
        'original_name',
        'path',
        'extension',
        'mime_type',
        'size',
    ];

    protected $hidden = [
        'fileable_type',
        'fileable_id',
    ];

    public function fileable()
    {
        return $this->morphTo();
    }
}
