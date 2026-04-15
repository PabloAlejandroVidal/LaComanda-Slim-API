<?php

use App\Middlewares\ErrorHandlerMiddleware;

return function ($app) {

    $app->addRoutingMiddleware();

    $container = $app->getContainer();

    $app->add($container->get(ErrorHandlerMiddleware::class));

    $app->addBodyParsingMiddleware();

    $app->addErrorMiddleware(false, true, true);
};