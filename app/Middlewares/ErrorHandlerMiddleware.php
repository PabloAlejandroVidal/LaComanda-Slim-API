<?php

namespace App\Middlewares;

use App\Exceptions\HttpBaseException;
use App\Http\ApiResponseResponder;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use RuntimeException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;

class ErrorHandlerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory
    ) {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        try {
            return $handler->handle($request);

        } catch (HttpBaseException $e) {
            return ApiResponseResponder::error(
                $this->responseFactory->createResponse(),
                $e->getStatusCode(),
                $e->getStatus(),
                $e->getMessage(),
                $e->getErrors()
            );

        } catch (HttpMethodNotAllowedException $e) {
            error_log((string) $e);
            $response = ApiResponseResponder::error(
                $this->responseFactory->createResponse(),
                405,
                'METHOD_NOT_ALLOWED',
                'Método no permitido'
            );

            return $response;

        } catch (HttpNotFoundException $e) {
            error_log((string) $e);
            
            return ApiResponseResponder::error(
                $this->responseFactory->createResponse(),
                404,
                'NOT_FOUND',
                'Recurso no encontrado'
            );
 
        } catch (\PDOException $e) {
            error_log((string) $e);

            return ApiResponseResponder::error(
                $this->responseFactory->createResponse(),
                500,
                'DATABASE_ERROR',
                'Ocurrió un error interno de base de datos'
            );

        } catch (RuntimeException $e) {
            error_log((string) $e);

            return ApiResponseResponder::error(
            $this->responseFactory->createResponse(),
                500,
                'SERVER_ERROR',
                'El endpoint configurado no es resolvible',
            );
        } catch (\Throwable $e) {
            error_log((string) $e);

            return ApiResponseResponder::error(
                $this->responseFactory->createResponse(),
                500,
                'SERVER_ERROR',
                'Error interno del servidor'
            );
        }
    }
}