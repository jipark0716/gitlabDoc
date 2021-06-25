<?php

namespace App\Gitlab;

use GuzzleHttp\Client as Guzzle;

class Client {

    use Project;

    /**
     * Http Client
     *
     * @var GuzzleHttp\Client
     */
    protected $client;

    public function __construct()
    {
        $this->client = new Guzzle([
            'headers' => [
                'PRIVATE-TOKEN' => config('services.gitlab.access_token')
            ],
            'base_uri' => config('services.gitlab.baseurl')
        ]);
    }

    /**
     * API call
     *
     * @return string $method
     * @return string $path
     * @return array
     */
    public function call($method, $path)
    {
        $response = $this->client->{$method}($path);
        return json_decode($response->getBody(), true);
    }

    /**
     * get 요청 전송
     *
     * @return string $path
     * @return array $queryString
     * @return array
     */
    public function get($path, $queryString = [])
    {
        if ($queryString != []) {
            $path .= '?'.http_build_query($queryString);
        }
        return $this->call('get', $path);
    }
}
