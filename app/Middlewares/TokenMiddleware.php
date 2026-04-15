<?php
namespace App\Middlewares;

use App\Domain\Empleado\EmpleadoType;
use App\Exceptions\ForbiddenException;
use App\Exceptions\UnauthorizedException;
use App\Interfaces\TokenVerifierInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class TokenMiddleware implements MiddlewareInterface
{
    /**
     * @param array<int, EmpleadoType|string> $requiredEmployeeTypes
     */
    public function __construct(
        private TokenVerifierInterface $tokenVerifier,
        private array $requiredEmployeeTypes
    ) {
        $this->requiredEmployeeTypes = array_map(
            fn (EmpleadoType|string $type): EmpleadoType => $this->normalizeEmployeeType($type),
            $this->requiredEmployeeTypes
        );
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!str_starts_with($authHeader, 'Bearer ')) {
            throw new UnauthorizedException('Token requerido');
        }

        $token = trim(substr($authHeader, 7));

        if ($token === '') {
            throw new UnauthorizedException('Token requerido');
        }

        $decoded = $this->tokenVerifier->verifyToken($token);

        if (!isset($decoded->data) || !is_object($decoded->data)) {
            throw new UnauthorizedException('Token inválido');
        }

        $payload = (array) $decoded->data;
        $employeeTypeValue = $payload['rol'] ?? null;

        if (!is_string($employeeTypeValue) || $employeeTypeValue === '') {
            throw new UnauthorizedException('Token inválido');
        }

        try {
            $employeeType = EmpleadoType::from($employeeTypeValue);
        } catch (\ValueError $e) {
            throw new UnauthorizedException('Rol inválido en token');
        }

        if (!in_array($employeeType, $this->requiredEmployeeTypes, true)) {
            throw new ForbiddenException('No tienes permisos suficientes');
        }

        $request = $request->withAttribute('decodedToken', $payload);

        return $handler->handle($request);
    }

    private function normalizeEmployeeType(EmpleadoType|string $type): EmpleadoType
    {
        if ($type instanceof EmpleadoType) {
            return $type;
        }

        try {
            return EmpleadoType::from($type);
        } catch (\ValueError $e) {
            throw new InvalidArgumentException("Rol requerido inválido: {$type}", 0, $e);
        }
    }
}