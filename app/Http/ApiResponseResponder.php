<?php

namespace App\Http;

use Psr\Http\Message\ResponseInterface as Response;

class ApiResponseResponder
{
    public static function respond(
        Response $res,
        int $statusCode,
        string $message,
        ?string $status = null,
        mixed $data = null,
        mixed $errors = null,
        ?array $meta = null
    ): Response {
        $isSuccess = $statusCode < 400;

        $payload = [
            'success' => $isSuccess,
            'status' => $status ?? ($isSuccess ? 'SUCCESS' : 'ERROR'),
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        $res->getBody()->write(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        return $res
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/json');
    }

    public static function success(
        Response $res,
        mixed $data = null,
        string $message = 'OK',
        int $statusCode = 200,
        string $status = 'SUCCESS',
        ?array $meta = null
    ): Response {
        return self::respond(
            $res,
            $statusCode,
            $message,
            $status,
            $data,
            null,
            $meta
        );
    }

    public static function created(
        Response $res,
        mixed $data,
        string $message = 'Recurso creado correctamente'
    ): Response {
        return self::respond(
            $res,
            201,
            $message,
            'CREATED',
            $data
        );
    }

    public static function noContent(Response $res): Response
    {
        return $res->withStatus(204);
    }

    public static function error(
        Response $res,
        int $statusCode,
        string $status,
        string $message,
        mixed $errors = null
    ): Response {
        return self::respond(
            $res,
            $statusCode,
            $message,
            $status,
            null,
            $errors
        );
    }

    public static function paginated(
        Response $res,
        array $data,
        array $pagination,
        string $message = 'OK'
    ): Response {
        return self::respond(
            $res,
            200,
            $message,
            'SUCCESS',
            $data,
            null,
            ['pagination' => $pagination]
        );
    }
}