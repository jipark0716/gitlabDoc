<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    public $fillable = ['id', 'namespace', 'root_url', 'readme_url', 'name', 'nickname'];
    const CREATED_AT = null;
    const UPDATED_AT = null;

    /**
     * file 관계성
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function file()
    {
        return $this->hasMany(RepositoryFile::class, 'repository_id')->orderBy('name');
    }

    /**
     * file namespace attribute
     *
     * @return array
     */
    public function getFileNamespaceAttribute()
    {
        $namespace = [];
        foreach ($this->file as $file) {
            if ($file->class) {
                $names = explode('\\', $file->class);
                $last = &$namespace;
                foreach ($names as $i => $name) {
                    if ($i === array_key_last($names)) {
                        $last[$name] = $file;
                    } else {
                        $last[$name] = $last[$name] ?? [];
                        $last = &$last[$name];
                    }
                }
            } else {
                $names = explode('/', $file->name);
                $last = &$namespace;
                foreach ($names as $i => $name) {
                    if ($i === array_key_last($names)) {
                        $last[$name] = $file;
                    } else {
                        $last[$name] = $last[$name] ?? [];
                        $last = &$last[$name];
                    }
                }
            }
        }
        return $namespace;
    }
}
