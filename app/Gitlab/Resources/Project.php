<?php

namespace App\Gitlab\Resources;

use GuzzleHttp\Client as Guzzle;

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

    /**
     *
     *
     *
     *
     */
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
            echo '_';
            $info = [];
            foreach (explode("\n", (string) $file->getContent()) as $row) {
                if (!isset($info['namespace']) && str_starts_with($row, 'namespace ')) {
                    $info['namespace'] = str_replace(['namespace ', ';'], '', $row);
                } elseif (!isset($info['class']) && str_starts_with($row, 'class ')) {
                    $row = preg_replace('/ *{ */', '', $row);
                    if (preg_match('/^ *(?<type>class|interface) (?<class>[a-zA-Z]{1,1000}).*extends (?<extend>[\\a-zA-Z]{1,1000})/', $row, $match)) {
                        $info['class'] = $match['class'];
                        $info['type'] = $match['type'];
                        if (isset($match['extend'])) {
                            $info['extend'] = $file->findImport($match['extend']);
                        }
                        $info['implement'] = preg_replace('/^ *(?<type>class|interface) (?<class>[a-zA-Z]{1,1000}).*extends (?<extend>[a-zA-Z]{1,1000})|implements| |/', '', $row) ?? null;
                        if ($info['implement']) {
                            $info['implement'] = $file->findImport($info['implement']);
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
            $result[$file->path]['function'] = [];
            for ($i = 0; $i < count($rows); $i++) {
                if (preg_match('/(?<public>public|protected|private|) *(?<static>static|) *function (?<name>[a-zA-Z]{1,1000}) *\(/', $rows[$i], $matchF)) {
                    $tempIndex = $i - 1;
                    $description = ['param' => []];
                    if (preg_match('/\*\//', $rows[$tempIndex])) {
                        while (--$tempIndex >= 0) {
                            $row = $rows[$tempIndex];
                            if (
                                preg_match('/^ *\* *\@param  *(?<type>[a-zA-Z\\\]{1,1000})  *(?<name>[a-zA-Z\\$]{1,1000})/', $row, $match) &&
                                isset($match['name']) && isset($match['type'])
                            ) {
                                $description['param'][] = [
                                    'type' => $match['type'],
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
                            } elseif (preg_match('/\/\*/', $row)) {
                                break;
                            } elseif (preg_replace('/^ *\* */', '', $row) != '') {
                                if (isset($description['comment'])) {
                                    $description['comment'] .= "\n".preg_replace('/^ *\* */', '', $row);
                                } else {
                                    $description['comment'] = preg_replace('/^ *\* */', '', $row);
                                }
                            }

                        }
                    }
                    $result[$file->path] = isset($result[$file->path]) ? $result[$file->path] : [];
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
        return $this->getFunctions();
        $namespace = $this->getNameSpaces();
    }
}
