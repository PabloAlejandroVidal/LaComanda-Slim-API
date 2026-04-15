<?php

namespace App\Services;

use App\Domain\Empleado\EmpleadoType;
use App\DTO\Request\UsuarioRequest;
use App\DTO\Response\AuthenticatedUserDTO;
use App\DTO\Response\LoginResponseDTO;
use App\DTO\Response\RolResponseDTO;
use App\Entities\TokenPayload;
use App\Exceptions\DataIntegrityException;
use App\Exceptions\LoginException;
use App\Interfaces\TokenGeneratorInterface;
use App\Repositories\EmpleadoRepository;
use App\Repositories\IngresoRepository;

class LoginService
{
    public function __construct(
        private EmpleadoRepository $empleadoRepository,
        private IngresoRepository $ingresoRepository,
        private TokenGeneratorInterface $tokenGenerator,
        private int $expiration
    ) {}

    public function login(UsuarioRequest $usuarioRequest): LoginResponseDTO
    {
        $empleado = $this->empleadoRepository->getEmpleadoAuthDataByEmail($usuarioRequest->email);

        if (!$empleado || !password_verify($usuarioRequest->clave, $empleado['clave'])) {
            throw new LoginException();
        }

        if ($empleado['estado'] === 'suspendido') {
            throw new LoginException('El empleado está suspendido y no puede iniciar sesión');
        }

        if ($empleado['estado'] === 'borrado') {
            throw new LoginException('El empleado fue dado de baja y no puede iniciar sesión');
        }

        if ($empleado['estado'] !== 'activo') {
            throw new LoginException('El empleado no tiene un estado válido para iniciar sesión');
        }

        $rolId = (int) $empleado['tipo_empleado_id'];

        try {
            $rol = EmpleadoType::fromId($rolId);
        } catch (\ValueError $e) {
            throw new DataIntegrityException(
                "Tipo de empleado inválido en persistencia: {$rolId}",
                previous: $e
            );
        }

        $this->ingresoRepository->registrarIngreso(
            (int) $empleado['id'],
            (new \DateTimeImmutable())->format('Y-m-d H:i:s')
        );

        $tokenPayload = new TokenPayload(
            (int) $empleado['id'],
            $empleado['nombre'],
            $empleado['email'],
            $rol->value
        );

        $token = $this->tokenGenerator->generateToken($tokenPayload);

        return new LoginResponseDTO(
            accessToken: $token,
            tokenType: 'Bearer',
            expiresIn: $this->expiration,
            user: new AuthenticatedUserDTO(
                id: (int) $empleado['id'],
                nombre: $empleado['nombre'],
                email: $empleado['email'],
                rol: new RolResponseDTO(
                    id: $rolId,
                    nombre: $rol->value
                )
            )
        );
    }

}