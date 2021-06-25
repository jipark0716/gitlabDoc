<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepositoryFile extends Model
{
    public $fillable = ['id', 'repository_id', 'name', 'class', 'type', 'implements', 'extend', 'abstract'];
    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $casts = [
        'abstract' => 'boolean',
    ];
}
