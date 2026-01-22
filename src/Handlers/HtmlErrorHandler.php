<?php

namespace App\Handlers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;
use Throwable;

class HtmlErrorHandler
{
    public function __construct(
        private ContainerInterface $container,
        private string $template,
        private int $status
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails
    ): ResponseInterface {
        $response = new Response($this->status);

        return render($this->container, $request, $response, $this->template);
    }
}
