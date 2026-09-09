<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa',
        'descripcion',
        'original_name',
        'filename',
        'path',
        'full_path',
        'url',
        'size_kb',
    ];
}
