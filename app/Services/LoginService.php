<?php
namespace App\Services;
use App\DTO\EmpleadoToken;
use App\DTO\LoginResult;
use App\DTO\TokenPayload;
use App\DTO\UsuarioRequest;
use App\Interfaces\TokenGeneratorInterface;
use App\Repositories\EmpleadoRepository;
use App\Repositories\PedidoRepository;
use App\Repositories\DetallePedidoRepository;
use App\Repositories\MesaRepository;
use App\Repositories\PermisoRepository;
use App\Exceptions\LoginException;

class LoginService
{
    public function __construct(
        private EmpleadoRepository $empleadoRepository,
        private TokenGeneratorInterface $tokenGenerator
    ) {}

    public function login(UsuarioRequest $usuarioRequest): array
    {
        $empleado = $this->empleadoRepository->getEmpleadoByEmail($usuarioRequest->email);

        if (!$empleado || !password_verify($usuarioRequest->clave, $empleado->clave)) {
            throw new LoginException("Credenciales inválidas.");
        }

        $tokenPayload = new TokenPayload(
            $empleado->id,
            $empleado->nombre,
            $empleado->email,
            $empleado->tipoEmpleadoId
        );

        $token = $this->tokenGenerator->generateToken($tokenPayload);

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'user' => [
                'id' => $empleado->id,
                'nombre' => $empleado->nombre,
                'email' => $empleado->email,
                'rol' => $empleado->tipoEmpleadoId
            ]
        ];
    }


}
?>
