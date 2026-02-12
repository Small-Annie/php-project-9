<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Routing\RouteContext;

function redirectTo(
    ServerRequestInterface $request,
    ResponseInterface $response,
    string $routeName,
    array $params = []
): ResponseInterface {
    $routeContext = RouteContext::fromRequest($request);
    $routeParser = $routeContext->getRouteParser();

    return $response
        ->withHeader('Location', $routeParser->urlFor($routeName, $params))
        ->withStatus(302);
}
