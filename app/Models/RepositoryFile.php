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
        'id' => 'string'
    ];

    /**
     * get path attribute
     *
     * @return array
     */
    public function getPathAttribute()
    {
        if ($this->class) {
            return explode('\\', $this->class);
        } else {
            return explode('/', $this->name);
        }
        return [];
    }

    /**
     * get file_name attribute
     *
     * @return string
     */
    public function getFileNameAttribute()
    {
        return last($this->path);
    }

    /**
     * 상속 받은 클래스 관계성
     *
     * @return Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function extendFile()
    {
        return $this->hasOne(self::class, 'class', 'extend');
    }

    /**
     * 상속 받은 인터페이스 관계성
     *
     * @return Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function implementFile()
    {
        return $this->hasOne(self::class, 'class', 'implements');
    }

    /**
     * function 관계성 지정
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function function()
    {
        return $this->HasMany(RepositoryFileFunction::class, 'file_id');
    }

    /**
     * repository 관계성 지정
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function repository()
    {
        return $this->belongsTo(Repository::class, 'repository_id', 'id');
    }

    /**
     * get fileLink attribute
     *
     * @return string
     */
    public function getLinkAttribute()
    {
        return $this->repository->root_url.'/-/blob/master/'.$this->name;
    }
}
