<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Dotenv\Dotenv;
use App\Database;
use App\Controllers\HomeController;
use App\Controllers\MenuController;
use App\Controllers\OrderController;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Middleware\AdminMiddleware;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// DB connect
Database::connect();
Database::seed();

// Create Slim app
$app = AppFactory::create();
$app->setBasePath('');

$errorMiddleware = $app->addErrorMiddleware(false, true, true);
$errorMiddleware->setDefaultErrorHandler(function () use ($app) {
    $response = $app->getResponseFactory()->createResponse();
    $twig = Twig::create(__DIR__ . '/templates', ['cache' => false]);
    return $twig->render($response->withStatus(500), 'error.html.twig');
});

// Twig
$twig = Twig::create(__DIR__ . '/templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

// Public routes
$app->get('/',        [HomeController::class, 'index']);
$app->get('/menu',    [MenuController::class, 'index']);
$app->get('/about',   [HomeController::class, 'about']);
$app->get('/contact', [HomeController::class, 'contact']);

// Order routes
$app->get('/order',              [OrderController::class, 'index']);
$app->post('/order/submit',      [OrderController::class, 'submit']);
$app->get('/order/confirm/{id}', [OrderController::class, 'confirm']);

// Auth routes
$app->get('/admin/login',  [AuthController::class, 'loginPage']);
$app->post('/admin/login', [AuthController::class, 'login']);
$app->get('/admin/logout', [AuthController::class, 'logout']);

// Admin routes (protected)
$app->group('/admin', function ($group) {
    $group->get('',                       [AdminController::class, 'dashboard']);
    $group->get('/orders',                [AdminController::class, 'orders']);
    $group->get('/orders/{id}',           [AdminController::class, 'orderDetail']);
    $group->post('/orders/{id}/status',   [AdminController::class, 'updateStatus']);
    $group->get('/products',              [AdminController::class, 'products']);
    $group->get('/products/create',       [AdminController::class, 'createProduct']);
    $group->post('/products/create',      [AdminController::class, 'storeProduct']);
    $group->get('/products/{id}/edit',    [AdminController::class, 'editProduct']);
    $group->post('/products/{id}/edit',   [AdminController::class, 'updateProduct']);
    $group->post('/products/{id}/delete', [AdminController::class, 'deleteProduct']);
    $group->get('/calendar',              [AdminController::class, 'calendar']);
    $group->post('/calendar/block',       [AdminController::class, 'blockDate']);
    $group->post('/calendar/unblock',     [AdminController::class, 'unblockDate']);
    $group->get('/analytics',             [AdminController::class, 'analytics']);
})->add(new AdminMiddleware());

$app->run();