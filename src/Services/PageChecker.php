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

        $h1Node = $crawler->filter('h1')->first();
        $titleNode = $crawler->filter('title')->first();
        $descriptionNode = $crawler->filter('meta[name="description"]')->first();

        return [
            'status_code' => $statusCode,
            'h1' => optional($h1Node->count() ? $h1Node : null)->text(),
            'title' => optional($titleNode->count() ? $titleNode : null)->text(),
            'description' => $descriptionNode->count()
                ? $descriptionNode->attr('content')
                : null,
        ];
    }
}
