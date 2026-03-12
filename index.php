<?php
require_once 'vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$container = new Container();
AppFactory::setContainer($container);

$app = AppFactory::createFromContainer($container);
$app->setBasePath('/Programacion3/trabajoPractico-Final/app');

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

require_once 'app/config/dependencies.php';
require_once 'routes.php';

$app->addBodyParsingMiddleware();
// se movió addBodyParsingMiddleware antes de run (revisar que no rompa el flujo)
$app->run();

?>