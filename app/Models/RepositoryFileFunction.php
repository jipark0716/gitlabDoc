<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepositoryFileFunction extends Model
{
    public $fillable = ['file_id', 'name', 'return_type', 'return_comment', 'comment', 'public', 'static'];
    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $casts = [
        'static' => 'boolean',
    ];
}
