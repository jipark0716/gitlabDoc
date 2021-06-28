<?php

namespace App\Gitlab\Resources;

use GuzzleHttp\Client as Guzzle;
use App\Models\{Repository, RepositoryFile, RepositoryFileFunction, RepositoryFileFunctionParam, RepositoryFileFunctionThrow};

class Project extends Resource {

    /**
     * 레파지토리 모든 파일 가져오기
     *
     * @return array
     */
    public function getFiles()
    {
        return \Gitlab::getProjectFiles($this->id);
    }

    /**
     * cache 용 key return
     *
     * @param string $path
     * @return string
     */
    public function cacheKey($path = null)
    {
        $result = 'gitlab_project_'.$this->id;
        if ($path) {
            $result .= '_'.$path;
        }
        return $result;
    }

    /**
     * project 내 모든 php file return
     *
     * @return array
     */
    public function getPhpFiles($root = null)
    {
        $result = [];
        foreach ($root ? $root->getContent() : $this->getFiles() as $file) {
            if ($file->type == 'tree') {
                $result = array_merge($result, $this->getPhpFiles($file));
            } elseif (str_ends_with($file->path, '.php') && !str_ends_with($file->path, '.blade.php')) {
                $result[] = $file;
            }
        }
        return $result;
    }

    public function __get($key)
    {
        if ($key == 'php_files') {
            return \Cache::rememberForever($this->cacheKey('files'), function() {
                return $this->getPhpFiles();
            });
        }
        return parent::__get(...func_get_args());
    }

    /**
     * project 낸 모든 class namespace return
     *
     * @return array
     */
    public function getNameSpaces()
    {
        $result = [];
        foreach ($this->php_files as $file) {
            $info = [];
            foreach (explode("\n", (string) $file->getContent()) as $row) {
                if (!isset($info['namespace']) && str_starts_with($row, 'namespace ')) {
                    $info['namespace'] = str_replace(['namespace ', ';'], '', $row);
                } elseif (!isset($info['class']) && preg_match('/^ *class|interface|trait|abstract /', $row)) {
                    if (preg_match('/^ *abstract */', $row)) {
                        $row = preg_replace('/^ *abstract */', '', $row);
                        $info['abstract'] = true;
                    }
                    $row = preg_replace('/ *{ */', '', $row);
                    if (
                        preg_match('/^ *(?<type>class|interface|trait) (?<class>[a-zA-Z]{1,1000}).*extends (?<extend>[\\a-zA-Z]{1,1000})/', $row, $match) ||
                        preg_match('/^ *(?<type>class|interface|trait) (?<class>[a-zA-Z]{1,1000})/', $row, $match)
                    ) {
                        $info['class'] = $match['class'];
                        $info['type'] = $match['type'];
                        if (isset($match['extend'])) {
                            $info['extend'] = $file->findImport($match['extend'], $info['namespace'] ?? null);
                        }
                        $info['implement'] = preg_replace('/^ *(?<type>class|interface) (?<class>[a-zA-Z]{1,1000}).*extends (?<extend>[a-zA-Z]{1,1000})|implements| |/', '', $row) ?? null;
                        if ($info['implement']) {
                            $info['implement'] = $file->findImport($info['implement'], $info['namespace'] ?? null);
                        }
                        break;
                    }
                }
            }
            if (isset($info['namespace']) && isset($info['class'])) {
                $result[$file->path] = [
                    'class' => $info['namespace']."\\".$info['class'],
                    'type' => $info['type'],
                    'extend' => $info['extend'] ?? null,
                    'implements' => $info['implement'] ?: null,
                    'abstract' => $info['abstract'] ?? false,
                ];
            }
        }
        return $result;
    }

    /**
     * 함수 목록 return
     *
     * @return array
     */
    public function getFunctions()
    {
        $result = [];
        foreach ($this->php_files as $file) {
            $inPhp = false;
            $rows = explode("\n", (string) $file->getContent());
            $result[$file->path] = [
                'function' => [],
                'id' => $file->id,
            ];
            for ($i = 0; $i < count($rows); $i++) {
                if (preg_match('/(?<public>public|protected|private|) *(?<static>static|) *function (?<name>[\_a-zA-Z]{1,1000}) *\(/', $rows[$i], $matchF)) {
                    $tempIndex = $i - 1;
                    $description = ['param' => [], 'throw' => [], 'return' => []];
                    if (preg_match('/^ *\*\//', $rows[$tempIndex])) {
                        while (--$tempIndex >= 0) {
                            $row = $rows[$tempIndex];
                            if (
                                preg_match('/^ *\* *\@param  *(?<type>[a-zA-Z\\\]{1,1000})  *(?<name>[a-zA-Z\\$]{1,1000})/', $row, $match) &&
                                isset($match['name']) && isset($match['type'])
                            ) {
                                $description['param'][] = [
                                    'type' => preg_replace('/^\\\/', '', $match['type']),
                                    'name' => $match['name'],
                                    'comment' => preg_replace('/^ *\* *\@param  *(?<type>[a-zA-Z\\\]{1,1000})  *(?<name>[a-zA-Z\\$]{1,1000}) */', '', $row),
                                ];
                            } elseif (
                                preg_match('/^ *\* *\@return (?<type>[a-zA-Z\\\]{1,1000})/', $row, $match) &&
                                isset($match['type'])
                            ) {
                                $description['return'] = [
                                    'type' => $match['type'],
                                    'comment' => preg_replace('/^ *\* *\@return (?<type>[a-zA-Z\\\]{1,1000}) */', '', $row),
                                ];
                            } elseif(
                                preg_match('/^ *\* *\@throws (?<type>[a-zA-Z\\\]{1,1000})/', $row, $match) &&
                                isset($match['type'])
                            ) {
                                $description['throw'][] = [
                                    'type' => $match['type'],
                                    'comment' => preg_replace('/^ *\* *\@throws (?<type>[a-zA-Z\\\]{1,1000}) */', '', $row),
                                ];
                            } elseif (preg_match('/\/\*/', $row)) {
                                break;
                            } elseif (preg_replace('/^ *\* */', '', $row) != '') {
                                if (isset($description['comment'])) {
                                    $description['comment'] = preg_replace('/^ *\* */', '', $row)."\n".$description['comment'];
                                } else {
                                    $description['comment'] = preg_replace('/^ *\* */', '', $row);
                                }
                            }
                        }
                    }
                    $description['param'] = array_reverse($description['param']);
                    $result[$file->path]['function'][$matchF['name']] = array_merge($description, [
                        'public' => $matchF['public'] ?? 'default',
                        'static' => !!$matchF['static'],
                    ]);
                }
            }
        }
        return $result;
    }

    /**
     * files info return
     *
     * @return array
     */
    public function getInfo()
    {
        return array_merge_recursive(
            $this->getNameSpaces(),
            $this->getFunctions()
        );
    }

    /**
     * 수집한 문서 저장
     *
     * @return void
     */
    public function save()
    {
        $repository = Repository::find($this->id) ?? new Repository([
            'id' => $this->id,
        ]);
        $repository->fill([
            'namespace' => $this->path_with_namespace,
            'root_url' => $this->web_url,
            'readme_url' => $this->readme_url,
            'name' => $this->name,
        ])->save();

        RepositoryFile::where([
            'repository_id' => $this->id,
        ])->delete();

        foreach ($this->getInfo() as $name => $info) {
            RepositoryFile::create([
                'id' => $info['id'],
                'repository_id' => $this->id,
                'name' => $name,
                'class' => $info['class'] ?? null,
                'type' => $info['type'] ?? null,
                'implements' => $info['implements'] ?? null,
                'extend' => $info['extend'] ?? null,
                'abstract' => $info['abstract'] ?? false,
            ]);
            RepositoryFileFunction::where([
                'file_id' => $info['id'],
            ])->delete();
            RepositoryFileFunctionParam::where([
                'file_id' => $info['id'],
            ])->delete();
            RepositoryFileFunctionThrow::where([
                'file_id' => $info['id'],
            ])->delete();
            foreach ($info['function'] ?? [] as $functionName => $function) {
                RepositoryFileFunction::create([
                    'file_id' => $info['id'],
                    'name' => $functionName,
                    'return_type' => $function['return']['type'] ?? null,
                    'return_comment' => $function['return']['comment'] ?? null,
                    'comment' => $function['comment'] ?? null,
                    'public' => $function['public'] ?? null,
                    'static' => $function['static'] ?? null,
                ]);
                foreach ($function['param'] ?? [] as $param) {
                    RepositoryFileFunctionParam::create([
                        'file_id' => $info['id'],
                        'function_name' => $functionName,
                        'name' => $param['name'] ?? null,
                        'type' => $param['type'] ?? null,
                        'comment' => $param['comment'] ?? null,
                    ]);
                }
                foreach ($function['throw'] ?? [] as $throw) {
                    RepositoryFileFunctionThrow::create([
                        'file_id' => $info['id'],
                        'function_name' => $functionName,
                        'type' => $throw['type'] ?? null,
                        'comment' => $throw['comment'] ?? null,
                    ]);
                }
            }
        }
    }
}
