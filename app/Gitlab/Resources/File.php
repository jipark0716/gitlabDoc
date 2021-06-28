<?php

namespace App\Gitlab\Resources;

class File extends Resource {
    /**
     * 파일 내용
     *
     * @var string
     */
    public $fileContent;
    /**
     * 프로젝트 ID
     *
     * @var int
     */
    public $projectId;

    /**
     * project Id set
     *
     * @param int $projectId
     * @return self
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
        return $this;
    }

    /**
     * 파일 내용 return
     *
     * @return mixed
     */
    public function getContent()
    {
        if ($this->fileContent) {
            return $this->fileContent;
        }
        if ($this->type == 'blob') {
            return ($this->fileContent = \Gitlab::getFileContent($this->projectId, $this->path));
        } elseif ($this->type == 'tree') {
            return ($this->fileContent = \Gitlab::getProjectFiles($this->projectId, $this->path));
        } else {
            throw new \Exception("지원하지 않는 타입입니다.", 1);
        }
    }

    /**
     * php import 된것 return
     *
     * @return array
     */
    public function getImports()
    {
        $result = [];
        foreach (explode("\n", (string) $this->getContent()) as $row) {
            if (str_starts_with($row, 'use ')) {
                $row = str_replace(['use ', ';'], '', $row);
                if (preg_match('/(?<namespace>.*){(?<class>.*)}/', $row, $match)) {
                    foreach (explode(',', $match['class']) as $class) {
                        if (($as = explode(' as ', $class)) && count($as) > 1) {
                            $result[preg_replace('/ /', '', $as[1])] = $match['namespace'].preg_replace('/ /', '', $as[0]);
                        } else {
                            $result[preg_replace('/ /', '', $class)] = $match['namespace'].preg_replace('/ /', '', $class);
                        }
                    }
                } elseif (($as = explode(" as ", $row)) && count($as) > 1) {
                    $result[last($as)] = $as[0];
                } else {
                    $result[last(explode("\\", $row))] = $row;
                }
            }
        }
        return $result;
    }

    /**
     * class full path return
     *
     * @param string $class
     * @param string $namespace
     * @return string
     */
    public function findImport($class, $namespace)
    {
        if (str_starts_with("\\", $class)) {
            return str_replace("\\", '', $class);
        }
        if (isset($this->getImports()[$class])) {
            return $this->getImports()[$class];
        } elseif ($namespace) {
            return $namespace.'\\'.$class;
        } else {
            return $class;
        }
    }
}
