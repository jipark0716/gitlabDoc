<?php

namespace App\Gitlab\Resources;

class Resource {
    /**
     * 원본
     *
     * @var array
     */
    protected $json;

    /**
     * @param array $json
     */
    public function __construct($json)
    {
        $this->json = $json;
    }

    public function __get($attr)
    {
        return $this->json[$attr] ?? null;
    }
}
