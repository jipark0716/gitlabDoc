<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepositoryFileFunctionParam extends Model
{
    use \Awobaz\Compoships\Compoships;

    public $fillable = ['file_id', 'function_name', 'name', 'type', 'comment'];
    const CREATED_AT = null;
    const UPDATED_AT = null;

    /**
     * get text attribute
     *
     * @return string
     */
    public function getTextAttribute()
    {
        return "{$this->type_link} {$this->name}";
    }

    /**
     * get type link attribute
     *
     * @return string
     */
    public function getTypeLinkAttribute()
    {
        if ($this->typeFile) {
            return "<a href=\"".route('document', [
                'repository' => $this->typeFile->repository_id,
                'file' => $this->typeFile->id,
            ])."\">{$this->type}</a>";
        }
        return $this->type;
    }

    /**
     * type 관계성
     *
     * @return Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function typeFile()
    {
        return $this->hasOne(RepositoryFile::class, 'class', 'type');
    }
}
