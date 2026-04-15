<?php
use App\Contracts\FotoStorageInterface;
use App\Contracts\TransactionManager;
use App\Databases\DatabaseManager;
use App\Infrastructure\LocalFotoStorage;
use App\Infrastructure\PdoTransactionManager;
use App\Interfaces\TokenGeneratorInterface;
use App\Interfaces\TokenVerifierInterface;
use App\Repositories\EmpleadoRepository;
use App\Repositories\IngresoRepository;
use App\Repositories\PedidoFotoRepository;
use App\Repositories\PedidoRepository;
use App\Services\LoginService;
use App\Services\PedidoFotoService;
use App\Services\TokenGenerator;
use App\Services\TokenVerifier;
use App\Services\Utils;
use App\Middlewares\TokenMiddleware;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;
use App\Middlewares\ErrorHandlerMiddleware;

return function ($container) {
    

    /* ==============================
    FOTO STORAGE
    ================================= */

    $container->set(FotoStorageInterface::class, function () {
        return new LocalFotoStorage(
            storageDir: dirname(__DIR__, 2) . '/public/uploads/pedidos',
            publicBasePath: '/public/uploads/pedidos'
        );
    });

    /* ==============================
    REPOSITORIES
    ================================= */

    $container->set(PedidoFotoRepository::class, function ($c) {
        return new PedidoFotoRepository(
            $c->get(PDO::class)
        );
    });

    /* ==============================
    PEDIDO FOTO
    ================================= */

    $container->set(PedidoFotoService::class, function ($c) {
        return new PedidoFotoService(
            $c->get(PedidoRepository::class),
            $c->get(PedidoFotoRepository::class),
            $c->get(EmpleadoRepository::class),
            $c->get(FotoStorageInterface::class)
        );
    });

    /* ==============================
    JWT CONFIG
    ================================= */

    $container->set('jwt.secret', fn() => $_ENV['JWT_SECRET']);
    $container->set('jwt.expiration', fn() => (int) $_ENV['JWT_EXPIRATION']);

    /* ==============================
    TOKEN SERVICES
    ================================= */

    $container->set(TokenGeneratorInterface::class, function ($c) {
        return new TokenGenerator(
            $c->get('jwt.secret'),
            $c->get('jwt.expiration')
        );
    });

    $container->set(TokenVerifierInterface::class, function ($c) {
        return new TokenVerifier(
            $c->get('jwt.secret')
        );
    });

    /* ==============================
    UTILS
    ================================= */

    $container->set(Utils::class, fn() => new Utils());

    /* ==============================
    DATABASE
    ================================= */

    $container->set(DatabaseManager::class, fn() => new DatabaseManager());
    $container->set(PDO::class, fn($c) => $c->get(DatabaseManager::class)->getDb());

    /* ==============================
    RESPONSE FACTORY
    ================================= */

    $container->set(ResponseFactoryInterface::class, function () {
        return new ResponseFactory();
    });

    /* ==============================
    MIDDLEWARE
    ================================= */

    $container->set(TokenMiddleware::class, function ($c) {
        return function (array $roles) use ($c) {
            return new TokenMiddleware(
                $c->get(TokenVerifierInterface::class),
                $roles
            );
        };
    });

    /* ==============================
    TRANSACTIONS
    ================================= */

    $container->set(TransactionManager::class, function ($c) {
        return new PdoTransactionManager(
            $c->get(PDO::class)
        );
    });

    /* ==============================
    LOGIN
    ================================= */

    $container->set(LoginService::class, function ($c) {
        return new LoginService(
            $c->get(EmpleadoRepository::class),
            $c->get(IngresoRepository::class),
            $c->get(TokenGeneratorInterface::class),
            $c->get('jwt.expiration')
        );
    });

    /* ==============================
    ERROR HANDLER
    ================================= */

    $container->set(ErrorHandlerMiddleware::class, function ($c) {
        return new ErrorHandlerMiddleware(
            $c->get(ResponseFactoryInterface::class)
        );
    });
};
