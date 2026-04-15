<?php

use DI\Container;

$container = new Container();

$dependencies = require __DIR__ . '/dependencies.php';
$dependencies($container);

return $container;