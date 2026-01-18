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
        if ($this->status >= 500) {
            $this->log($exception);
        }

        $response = new Response($this->status);

        return render($this->container, $request, $response, $this->template);
    }

    private function log(Throwable $exception): void
    {
        $message = sprintf(
            "[%s] %s in %s:%d\n%s\n\n",
            date('Y-m-d H:i:s'),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        file_put_contents(
            __DIR__ . '/../../storage/logs/app.log',
            $message,
            FILE_APPEND
        );
    }
}
