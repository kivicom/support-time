<?php

use App\Controllers\FormController;
use App\Controllers\ReportController;
use App\Services\AppLogger;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->load();
}

define('BASE_PATH', $_ENV['BASE_PATH'] ?? '');

$app = AppFactory::create();
$app->setBasePath(BASE_PATH);
$app->addBodyParsingMiddleware();

$twig = Twig::create(__DIR__ . '/../app/Views', ['cache' => false]);
$twig->getEnvironment()->addGlobal('base_path', $app->getBasePath());
$app->add(TwigMiddleware::create($app, $twig));

// Все маршруты обрабатываются через /
$app->map(['GET', 'POST'], '/', function ($request, $response) use ($twig) {
    $action = $_GET['action'] ?? $_POST['action'] ?? 'form';

    return match ($action) {
        'report' => (new ReportController($twig))->index($request, $response),
        default  => (new FormController($twig))->index($request, $response),
    };
});

$app->run();
