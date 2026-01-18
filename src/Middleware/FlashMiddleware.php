<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Flash\Messages;

class FlashMiddleware
{
    public function __construct(private Messages $flash)
    {
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withAttribute('flash', $this->flash->getMessages());

        return $handler->handle($request);
    }
}
