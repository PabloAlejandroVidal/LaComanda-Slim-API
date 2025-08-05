<?php

use App\Databases\DatabaseManager;
use App\Interfaces\TokenGeneratorInterface;
use App\Interfaces\TokenVerifierInterface;
use App\Services\TokenGenerator;
use App\Services\TokenVerifier;
use App\Services\Utils;
use App\Middlewares\TokenMiddleware;

$clave = 'claveSecreta';

$container->set(TokenVerifierInterface::class, fn() => new TokenVerifier($clave));
$container->set(TokenGeneratorInterface::class, fn() => new TokenGenerator($clave));
$container->set(Utils::class, fn() => new Utils());


// Configurar el generador de tokens
$container->set(TokenGenerator::class, value: fn() => new TokenGenerator($clave));

// Configurar el verificador de tokens
$container->set(TokenVerifier::class, value: fn() => new TokenVerifier($clave));

// Configurar la base de datos
$container->set(DatabaseManager::class, fn() => new DatabaseManager());
$container->set(PDO::class, fn($c) => $c->get(DatabaseManager::class)->getDb());

// Configurar middlewares

$container->set(TokenMiddleware::class, function ($container): callable {
    return function(array $roles) use ($container) {
        return new TokenMiddleware(
            $container->get(TokenVerifier::class),
            $roles
        );
    };
});
