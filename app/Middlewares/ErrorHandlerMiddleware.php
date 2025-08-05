<?php
namespace App\Middlewares;
use App\Exceptions\HttpException;
use App\Http\ResponseHelper;
use App\Interfaces\TokenVerifierInterface;
use App\Repositories\PermisoRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Respect\Validation\Exceptions\NestedValidationException;
use Slim\Psr7\Response as SlimResponse;

class ErrorHandlerMiddleware implements MiddlewareInterface
{

    public function __construct(private ResponseFactoryInterface $responseFactory) {}


    public function process(Request $request, RequestHandler $handler): Response
    {
        try {
            return $handler->handle($request);

        }
        catch (\Throwable $e) {
            if ($e instanceof HttpException) {
                return ResponseHelper::respond($this->responseFactory->createResponse(), $e->getStatusCode(), $e->getMessage());
            }
            return ResponseHelper::respond($this->responseFactory->createResponse(), 500, 'Error interno del servidor' , "SERVER_ERROR");
        }
    }
}



?>