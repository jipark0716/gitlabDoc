<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepositoryFileFunction extends Model
{
    use \Awobaz\Compoships\Compoships;

    public $fillable = ['file_id', 'name', 'return_type', 'return_comment', 'comment', 'public', 'static'];
    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $casts = [
        'static' => 'boolean',
    ];

    /**
     * param 관계성 지정
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function param()
    {
        return $this->hasMany(RepositoryFileFunctionParam::class, ['file_id', 'function_name'], ['file_id', 'name']);
    }

    /**
     * file 관계성
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file()
    {
        return $this->BelongsTo(RepositoryFile::class, 'file_id', 'id');
    }
}
