<?php

use App\Controllers\FormController;
use App\Controllers\ReportController;
use App\Controllers\UploadController;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Загрузка .env
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Базовый путь
define('BASE_PATH', $_ENV['BASE_PATH']);

// Создание приложения
$app = AppFactory::create();
$app->setBasePath(BASE_PATH);

// Middleware
$app->addBodyParsingMiddleware();

$twig = Twig::create(__DIR__ . '/../app/Views', ['cache' => false]);
$twig->getEnvironment()->addGlobal('base_path', $app->getBasePath());
$app->add(TwigMiddleware::create($app, $twig));


// Контроллеры
$formController = new FormController($twig);
$reportController = new ReportController($twig);
$uploadController = new UploadController();

// Маршруты
$app->map(['GET', 'POST'], '/', [$formController, 'index']);
$app->post('/upload', [$uploadController, 'handle']);
$app->map(['GET', 'POST'], '/report', [$reportController, 'index']);

$app->run();
