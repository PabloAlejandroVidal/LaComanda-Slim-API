<?php

namespace App\Http;

use Psr\Http\Message\ResponseInterface as Response;

class JsonApiResponseHelper
{
    public static function respondWithData(Response $res, array|object $data, array $meta = [], int $statusCode = 200): Response
    {
        $payload = ['data' => $data];

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        return self::respond($res, $payload, $statusCode);
    }
        public static function respondWithCollection(Response $res, string $type, array $resources): Response
    {
        $data = array_map(function ($item) use ($type) {
            return [
                'type' => $type,
                'id' => (string)($item['id'] ?? null),
                'attributes' => array_diff_key($item, ['id' => true])
            ];
        }, $resources);

        return self::respond($res, ['data' => $data], 200);
    }


    public static function respondWithCreated(Response $res, array|object $data, string $location): Response
    {
        $payload = ['data' => $data];

        $res = self::respond($res, $payload, 201);

        if ($location) {
            $res = $res->withHeader('Location', $location);
        }

        return $res;
    }

    public static function respondWithPaginatedData(Response $res, array $data, array $paginationMeta): Response
    {
        return self::respondWithData($res, $data, ['pagination' => $paginationMeta]);
    }

    public static function respondWithError(Response $res, int $statusCode, string $title, string $detail, string $code): Response
    {
        $error = [
            'status' => (string)$statusCode,
            'title' => $title
        ];

        if ($detail !== null) {
            $error['detail'] = $detail;
        }

        if ($code !== null) {
            $error['code'] = $code;
        }

        return self::respond($res, ['errors' => [$error]], $statusCode);
    }

    private static function respond(Response $res, array $payload, int $statusCode): Response
    {
        $res->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $res
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/vnd.api+json');
    }
    public static function respondWithResource(Response $res, string $type, array $resource, int $statusCode = 200
    ): Response {
        $payload = [
            'data' => [
                'type' => $type,
                'id' => (string)($resource['id'] ?? null),
                'attributes' => array_diff_key($resource, ['id' => true])
            ]
        ];

        return self::respond($res, $payload, $statusCode);
    }

    public static function respondWithCreatedResource(Response $res, string $type, array $resource, string $location): Response {
        $response = self::respondWithResource($res, $type, $resource, 201);

        if ($location) {
            $response = $response->withHeader('Location', $location);
        }

        return $response;
    }

}
