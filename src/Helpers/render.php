<?php

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Routing\RouteContext;

function render(
    ContainerInterface $container,
    ServerRequestInterface $request,
    ResponseInterface $response,
    string $template,
    array $params = []
): ResponseInterface {
    $renderer = $container->get('renderer');

    $routeContext = RouteContext::fromRequest($request);
    $routeParser = $routeContext->getRouteParser();

    $route = $routeContext->getRoute();
    $routeName = $route?->getName();

    return $renderer->render($response, $template, array_merge($params, [
        'flash' => $request->getAttribute('flash', []),
        'activePage' => $routeName,
        'urlFor' => fn(string $name, array $data = [])
        => $routeParser->urlFor($name, $data),
    ]));
}
