<?php

namespace App\Controllers;

use App\Http\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\{
    Validator as v,
    Exceptions\NestedValidationException
};
use Respect\Validation\Validatable;


class Controller
{
/*     protected function respondWithJson(Response $response, array $data, int $statusCode = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withStatus($statusCode)->withHeader('Content-Type', 'application/json');
    }
    protected function handleValidationErrors(Response $response, NestedValidationException $exception): Response
    {
        return $this->respond($response, 422, $exception->getMessages());
    }
    protected function respond(Response $res, int $statusCode, string $message = null, mixed $data = null, mixed $errors = null): Response
    {
        return ResponseHelper::respond($res, $statusCode, $message, $data, $errors);
    }

    protected function validate(array $input, Validatable $schema): array
    {
        try {
            return $schema->assert($input) ?: $input;
        } catch (NestedValidationException $e) {
            throw new \DomainException($e->getFullMessage());
        }
    }

    protected function getToken($req) {
            return $req->getAttribute('decodedToken');
    }
 */
}
