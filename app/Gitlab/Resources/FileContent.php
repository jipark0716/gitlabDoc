<?php

namespace App\Gitlab\Resources;

use GuzzleHttp\Client as Guzzle;

class FileContent extends Resource {

    public function __toString()
    {
        if ($this->encoding == 'base64') {
            return base64_decode($this->content);
        } else {
            throw new \Exception("지원하지 않는 인코딩", 1);
        }
    }
}
