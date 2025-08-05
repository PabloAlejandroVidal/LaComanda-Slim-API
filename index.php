<?php
require_once 'vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); // o dirname(__DIR__) según tu estructura
$dotenv->load();

$container = new Container();
AppFactory::setContainer($container);

$app = AppFactory::createFromContainer($container);
$app->setBasePath('/Programacion3/trabajoPractico-Final/app');

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

require_once 'app/config/dependencies.php';
require_once 'routes.php';

$app->run();
// mover addBodyParsingMiddleware() antes de run()
$app->addBodyParsingMiddleware();

?>