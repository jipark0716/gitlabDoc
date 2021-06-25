<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepositoryFileFunctionParam extends Model
{
    public $fillable = ['file_id', 'function_name', 'name', 'type', 'comment'];
    const CREATED_AT = null;
    const UPDATED_AT = null;
}
