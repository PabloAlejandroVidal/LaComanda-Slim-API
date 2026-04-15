<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$container = require __DIR__ . '/app/bootstrap/container.php';

$appFactory = require __DIR__ . '/app/bootstrap/app.php';
$app = $appFactory($container);

(require __DIR__ . '/app/bootstrap/middleware.php')($app);
(require __DIR__ . '/app/bootstrap/routes.php')($app);

date_default_timezone_set('America/Argentina/Buenos_Aires');

$app->run();