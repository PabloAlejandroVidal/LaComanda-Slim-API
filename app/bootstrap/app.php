<?php

use Slim\Factory\AppFactory;

return function ($container) {

    AppFactory::setContainer($container);

    $app = AppFactory::createFromContainer($container);

    $app->setBasePath('/Programacion3/trabajoPractico-Final');

    return $app;
};