<?php

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Slim\Flash\Messages;
use Carbon\Carbon;
use DI\Container;

use App\Utils\UrlNormalizer;
use App\Validators\UrlValidator;
use App\Repositories\UrlRepository;
use App\Repositories\UrlCheckRepository;
use App\Middleware\FlashMiddleware;
use App\Handlers\HtmlErrorHandler;

session_start();

$container = new Container();

$container->set(PDO::class, function () {
    $databaseUrl = getenv('DATABASE_URL');
    $parsedDatabaseUrl = parse_url($databaseUrl);

    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        $parsedDatabaseUrl['host'],
        $parsedDatabaseUrl['port'] ?? 5432,
        ltrim($parsedDatabaseUrl['path'], '/')
    );

    $conn = new PDO(
        $dsn,
        $parsedDatabaseUrl['user'] ?? null,
        $parsedDatabaseUrl['pass'] ?? null
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $conn;
});

$container->set('flash', function () {
    return new Messages();
});

$container->set('renderer', function () {
    $renderer = new PhpRenderer(__DIR__ . '/../templates');
    $renderer->setLayout('layout.phtml');

    return $renderer;
});

$app = AppFactory::createFromContainer($container);

$app->addRoutingMiddleware();
$app->add(new FlashMiddleware($container->get('flash')));

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class,
    new HtmlErrorHandler($container, 'errors/404.html', 404)
);
$errorMiddleware->setDefaultErrorHandler(
    new HtmlErrorHandler($container, 'errors/500.html', 500)
);

$app->get('/', function (Request $request, Response $response) {
    return render($this, $request, $response, 'index.phtml');
})->setName('home');

$app->post('/urls', function (Request $request, Response $response) {
    $flash = $this->get('flash');
    $urlRepository = $this->get(UrlRepository::class);

    $data = $request->getParsedBody()['url'] ?? [];
    $errors = UrlValidator::validate($data);

    if (!empty($errors)) {
        return render($this, $request, $response, 'index.phtml', [
            'errors' => $errors,
            'url' => $data['name'] ?? ''
        ]);
    }

    $normalizedUrl = UrlNormalizer::normalize($data['name']);

    if ($existing = $urlRepository->findByName($normalizedUrl)) {
        $flash->addMessage('success', 'Страница уже существует');
        return redirectTo($request, $response, 'urls.show', ['id' => $existing['id']]);
    }

    $id = $urlRepository->create($normalizedUrl, Carbon::now()->format('Y-m-d H:i:s'));
    $flash->addMessage('success', 'Страница успешно добавлена');
    return redirectTo($request, $response, 'urls.show', ['id' => $id]);
})->setName('urls.store');

$app->get('/urls', function (Request $request, Response $response) {
    $urlRepository = $this->get(UrlRepository::class);

    $urls = $urlRepository->getAll();

    return render($this, $request, $response, 'urls/index.phtml', [
        'urls' => $urls
    ]);
})->setName('urls.index');

$app->get('/urls/{id}', function (Request $request, Response $response, array $args) {
    $urlRepository = $this->get(UrlRepository::class);

    $id = (int) $args['id'];
    $url = $urlRepository->find($id);

    if ($url === null) {
        throw new HttpNotFoundException($request);
    }

    $checkRepository = $this->get(UrlCheckRepository::class);
    $checks = $checkRepository->findByUrlId($id);

    return render($this, $request, $response, 'urls/show.phtml', [
        'url' => $url,
        'checks' => $checks,
    ]);
})->setName('urls.show');

$app->post('/urls/{id}/checks', function (Request $request, Response $response, array $args) {
    $flash = $this->get('flash');
    $checkRepository = $this->get(UrlCheckRepository::class);

    $urlId = (int) $args['id'];

    $checkRepository->create($urlId, Carbon::now()->format('Y-m-d H:i:s'));
    $flash->addMessage('success', 'Страница успешно проверена');
    return redirectTo($request, $response, 'urls.show', ['id' => $urlId]);
})->setName('urls.checks.store');;

$app->run();
