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

    $content = $renderer->fetch($template, $params);

    $path = $request->getUri()->getPath();

    $activePage = match ($path) {
        '/' => 'home',
        '/urls' => 'urls',
        default => null,
    };

    return $renderer->render($response, 'layout.phtml', [
        'content' => $content,
        'flash' => $request->getAttribute('flash', []),
        'activePage' => $activePage,
    ]);
}
