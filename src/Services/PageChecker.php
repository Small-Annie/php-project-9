<?php

namespace App\Services;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

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

    public function check(string $url): array
    {
        $response = $this->client->get($url);

        $statusCode = $response->getStatusCode();
        $html = (string) $response->getBody();

        $crawler = new Crawler($html);

        return [
            'status_code' => $statusCode,
            'h1' => optional($crawler->filter('h1')->first())->text(),
            'title' => optional($crawler->filter('title')->first())->text(),
            'description' => optional(
                $crawler->filter('meta[name="description"]')->first()
            )->attr('content'),
        ];
    }
}
