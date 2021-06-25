<?php

namespace App\Gitlab;

use GuzzleHttp\Client as Guzzle;

trait Project {

    /**
     * find project by id
     *
     * @param int $id
     * @return App\Gitlab\Resources\Project
     */
    public function getProject($id, $recache = false)
    {
        $cacheKey = 'gitlab_project_'.$id;

        if ($recache === false && \Cache::get($cacheKey)) {
            return \Cache::get($cacheKey);
        }
        \Cache::forget($cacheKey.'_files');
        $project = $this->getProjects([
            'id_after' => $id - 1,
            'id_before' => $id + 1,
        ])[0];
        \Cache::put($cacheKey, $project);
        return $project;
    }

    /**
     * ProjectList return
     *
     * @return array
     */
    public function getProjects($option)
    {
        $result = [];
        while ($projects = $this->get('projects', array_merge([
            'order_by' => 'id',
            'sort' => 'asc',
            'id_after' => isset($projects) ? last($projects)['id'] : 0,
        ], $option))) {
            foreach ($projects as $project) {
                $result[] = new Resources\Project($project);
            }
            if (count($projects) < 20) break;
        }
        return $result;
    }

    /**
     * 레파지토리 모든 파일 가져오기
     *
     * @param int $projectId
     * @return array
     */
    public function getProjectFiles($projectId, $path = null)
    {
        $page = 1;
        $result = [];
        while ($files = $this->get("projects/{$projectId}/repository/tree", [
            'ref' => 'master',
            'per_page' => '100',
            'page' => $page++,
            'path' => $path,
        ])) {
            foreach ($files as $file) {
                $result[] = (new Resources\File($file))->setProjectId($projectId);
            }
            if (count($result) < 100) break;
        }
        return $result;
    }

    /**
     * 파일 컨텐츠
     *
     * @param int $projectId
     * @param string $path
     * @return App\Gitlab\Resources\FileContent
     */
    public function getFileContent($projectId, $path)
    {
        return new Resources\FileContent($this->get("projects/{$projectId}/repository/files/".urlencode($path), [
            'ref' => 'master'
        ]));
    }
}
