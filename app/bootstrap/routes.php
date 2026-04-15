<?php
return function ($app) {
    
    $container = $app->getContainer();
    $routes = require __DIR__ . '/../routes.php';

    $routes($app, $container);

};