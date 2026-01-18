<?php

namespace App\Utils;

use Illuminate\Support\Str;

class UrlNormalizer
{
    public static function normalize(string $url): string
    {
        return Str::of($url)
            ->trim()
            ->lower()
            ->prepend(
                Str::startsWith($url, ['http://', 'https://']) ? '' : 'http://'
            )
            ->pipe(function (string $url) {
                $parsed = parse_url($url);
                return "{$parsed['scheme']}://{$parsed['host']}";
            })
            ->toString();
    }
}
