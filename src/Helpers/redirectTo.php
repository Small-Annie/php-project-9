<?php

use Slim\Routing\RouteContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
