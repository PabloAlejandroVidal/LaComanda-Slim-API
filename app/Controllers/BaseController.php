<?php

namespace App\Controllers;

use App\Contracts\ValidatableRequest;
use App\Entities\TokenPayload;
use App\Exceptions\BadRequestException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\UnsupportedMediaTypeException;
use App\Http\ApiResponseResponder;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

abstract class BaseController
{
    protected function ok(
        Response $response,
        mixed $data = null,
        string $message = 'OK',
        string $status = 'SUCCESS',
        ?array $meta = null
    ): Response {
        return ApiResponseResponder::success(
            $response,
            $data,
            $message,
            200,
            $status,
            $meta
        );
    }

    protected function created(
        Response $response,
        mixed $data,
        string $message = 'Recurso creado correctamente'
    ): Response {
        return ApiResponseResponder::created($response, $data, $message);
    }

    protected function noContent(Response $response): Response
    {
        return ApiResponseResponder::noContent($response);
    }

    protected function fail(
        Response $response,
        int $statusCode,
        string $status,
        string $message,
        mixed $errors = null
    ): Response {
        return ApiResponseResponder::error(
            $response,
            $statusCode,
            $status,
            $message,
            $errors
        );
    }

    protected function paginated(
        Response $response,
        array $data,
        array $pagination,
        string $message = 'OK'
    ): Response {
        return ApiResponseResponder::paginated(
            $response,
            $data,
            $pagination,
            $message
        );
    }

    protected function getRouteId(array $args, string $name = 'id'): string
    {
        $value = $args[$name] ?? null;

        if (!is_string($value) || trim($value) === '') {
            throw new BadRequestException("Parámetro de ruta '{$name}' inválido o ausente");
        }

        return $value;
    }

    protected function getTokenPayload(Request $request): TokenPayload
    {
        $decodedToken = $request->getAttribute('decodedToken');

        if (!is_array($decodedToken)) {
            throw new UnauthorizedException('Token inválido');
        }

        return TokenPayload::fromArray($decodedToken);
    }

    protected function getJsonBodyOrFail(Request $request): array
    {
        $contentType = strtolower(trim($request->getHeaderLine('Content-Type')));
        $mediaType = explode(';', $contentType)[0];

        if ($mediaType !== 'application/json') {
            throw new UnsupportedMediaTypeException(
                'El Content-Type debe ser application/json'
            );
        }

        $body = $request->getParsedBody();

        if ($body === null) {
            throw new BadRequestException('Body JSON inválido o ausente');
        }

        if (!is_array($body)) {
            throw new BadRequestException('El body debe ser un objeto JSON válido');
        }

        return $body;
    }

    protected function getNonEmptyJsonBodyOrFail(Request $request): array
    {
        $body = $this->getJsonBodyOrFail($request);

        if ($body === []) {
            throw new BadRequestException('Body JSON vacío');
        }

        return $body;
    }

    /**
     * @template T of ValidatableRequest
     * @param class-string<T> $dtoClass
     * @return T
     */
    protected function getJsonDtoOrFail(Request $request, string $dtoClass): ValidatableRequest
    {
        if (!is_subclass_of($dtoClass, ValidatableRequest::class)) {
            throw new \LogicException(
                "El DTO [$dtoClass] debe implementar " . ValidatableRequest::class
            );
        }

        return $dtoClass::fromArray(
            $this->getJsonBodyOrFail($request)
        );
    }

    protected function getRequiredDateRange(Request $request): array
    {
        $query = $request->getQueryParams();

        $fromRaw = isset($query['from']) ? trim((string) $query['from']) : '';
        $toRaw   = isset($query['to']) ? trim((string) $query['to']) : '';

        if ($fromRaw === '' || $toRaw === '') {
            throw new BadRequestException("Debe enviar parámetros 'from' y 'to'");
        }

        $from = $this->parseDateValueOrFail($fromRaw, 'from', false);
        $to   = $this->parseDateValueOrFail($toRaw, 'to', true);

        if ($from > $to) {
            throw new BadRequestException("El parámetro 'from' no puede ser mayor que 'to'");
        }

        return [
            $from->format('Y-m-d H:i:s'),
            $to->format('Y-m-d H:i:s'),
        ];
    }

    private function parseDateValueOrFail(
        string $value,
        string $paramName,
        bool $endOfDayIfDateOnly
    ): DateTimeImmutable {
        $formats = [
            ['!Y-m-d', true],
            ['!Y-m-d H:i', false],
            ['!Y-m-d H:i:s', false],
            ['!Y-m-d\TH:i', false],
            ['!Y-m-d\TH:i:s', false],
        ];

        foreach ($formats as [$format, $isDateOnly]) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            $errors = DateTimeImmutable::getLastErrors();

            $hasErrors = $errors !== false
                && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0);

            if ($date !== false && !$hasErrors) {
                if ($isDateOnly) {
                    return $endOfDayIfDateOnly
                        ? $date->setTime(23, 59, 59)
                        : $date->setTime(0, 0, 0);
                }

                return $date;
            }
        }

        throw new BadRequestException(
            "El parámetro '{$paramName}' debe tener formato YYYY-MM-DD, YYYY-MM-DD HH:MM[:SS] o YYYY-MM-DDTHH:MM[:SS]"
        );
    }

    protected function getUploadedFileOrFail(Request $request, string $field): UploadedFileInterface
    {
        $uploadedFiles = $request->getUploadedFiles();
        $file = $uploadedFiles[$field] ?? null;

        if (!$file instanceof UploadedFileInterface) {
            throw new BadRequestException("El campo '{$field}' es obligatorio");
        }

        if ($file->getError() === UPLOAD_ERR_NO_FILE) {
            throw new BadRequestException("Debe enviarse un archivo en el campo '{$field}'");
        }

        return $file;
    }
}