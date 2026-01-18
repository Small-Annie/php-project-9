<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

function redirectTo(
    $app,
    ServerRequestInterface $request,
    ResponseInterface $response,
    string $routeName,
    array $params = []
): ResponseInterface {
    $routeParser = $app->getRouteCollector()->getRouteParser();

    return $response
        ->withHeader('Location', $routeParser->urlFor($routeName, $params))
        ->withStatus(302);
}
