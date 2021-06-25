<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    public $fillable = ['id', 'namespace', 'root_url', 'readme_url', 'name', 'nickname'];
    const CREATED_AT = null;
    const UPDATED_AT = null;
}
