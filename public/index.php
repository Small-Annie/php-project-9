<?php

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Carbon\Carbon;
use DI\Container;

use App\Utils\UrlNormalizer;
use App\Validators\UrlValidator;
use App\Repositories\UrlRepository;
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
    return new Slim\Flash\Messages();
});

$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$app = AppFactory::createFromContainer($container);

$app->addRoutingMiddleware();
$app->add(new FlashMiddleware($container->get('flash')));

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setErrorHandler(HttpNotFoundException::class, new HtmlErrorHandler($container, 'errors/404.phtml', 404));
$errorMiddleware->setDefaultErrorHandler(new HtmlErrorHandler($container, 'errors/500.phtml', 500));

$app->get('/', function (Request $request, Response $response) {
    return render($this, $request, $response, 'index.phtml');
})->setName('home');

$app->post('/urls', function (Request $request, Response $response) {
    $flash = $this->get('flash');
    $repository = $this->get(UrlRepository::class);

    $data = $request->getParsedBody()['url'] ?? [];
    $errors = UrlValidator::validate($data);

    if (!empty($errors)) {
        return render($this, $request, $response, 'index.phtml', [
            'errors' => $errors,
            'url' => $data['name'] ?? ''
        ]);
    }

    $normalizedUrl = UrlNormalizer::normalize($data['name']);

    if ($existing = $repository->findByName($normalizedUrl)) {
        $flash->addMessage('success', 'Страница уже существует');
        return redirectTo($app, $request, $response, 'urls.show', ['id' => $existing['id']]);
    }

    $id = $repository->create($normalizedUrl, Carbon::now()->format('Y-m-d H:i:s'));
    $flash->addMessage('success', 'Страница успешно добавлена');
    return redirectTo($app, $request, $response, 'urls.show', ['id' => $id]);
})->setName('urls.store');

$app->get('/urls', function (Request $request, Response $response) {
    $repository = $this->get(UrlRepository::class);

    $urls = collect($repository->getAll())
        ->sortByDesc('created_at')
        ->values();

    return render($this, $request, $response, 'urls/index.phtml', [
        'urls' => $urls
    ]);
})->setName('urls.index');

$app->get('/urls/{id}', function (Request $request, Response $response, array $args) {
    $repository = $this->get(UrlRepository::class);

    $id = (int) $args['id'];
    $url = $repository->find($id);

    if ($url === null) {
        throw new HttpNotFoundException($request);
    }

    return render($this, $request, $response, 'urls/show.phtml', [
        'url' => $url,
        'checks' => [], // позже
    ]);
})->setName('urls.show');

$app->run();
