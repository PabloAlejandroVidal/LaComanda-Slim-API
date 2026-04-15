<?php

namespace App\Middlewares;

use App\Exceptions\UnsupportedMediaTypeException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class RequireJsonMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $contentType = strtolower(trim($request->getHeaderLine('Content-Type')));

        // tolera "application/json; charset=utf-8"
        if ($contentType === '' || !str_starts_with($contentType, 'application/json')) {
            echo $contentType;
            throw new UnsupportedMediaTypeException(
                'El Content-Type debe ser application/json'
            );
        }

        return $handler->handle($request);
    }
}