<?php

namespace App\Services;

use GuzzleHttp\Client;

class PageChecker
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 5.0,
            'http_errors' => false,
        ]);
    }

    public function check(string $url): int
    {
        $response = $this->client->get($url);
        return $response->getStatusCode();
    }
}
