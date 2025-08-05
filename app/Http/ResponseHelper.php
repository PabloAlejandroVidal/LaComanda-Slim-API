<?php
namespace App\Http;

use Psr\Http\Message\ResponseInterface as Response;

class ResponseHelper
{
    public static function respond(
        Response $res,
        int $statusCode,
        string $message = null,
        string $status = null,
        mixed $data = null,
        mixed $errors = null
    ): Response {
        $isSuccess = $statusCode < 400;

        $payload = [
            'success' => $isSuccess,
            'status' => $status ?? ($isSuccess ? 'success' : 'error')
        ];

        if ($message !== null) $payload['message'] = $message;
        if ($data !== null) $payload['data'] = $data;
        if ($errors !== null) $payload['errors'] = $errors;

        $res->getBody()->write(json_encode($payload));
        return $res
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/json');
    }
}
