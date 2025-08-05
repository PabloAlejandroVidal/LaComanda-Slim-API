<?php

namespace App\Controllers;

use App\DataAccess\DataAccess;
use App\DTO\EmpleadoToken;
use App\DTO\UsuarioRequest;
use App\Http\JsonApiResponseHelper;
use App\Interfaces\TokenGeneratorInterface;
use App\Repositories\EmpleadoRepository;
use App\Services\AccessService;
use App\Services\AuthorizationService;
use App\Services\LoginService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Databases\DatabaseManager;
use App\Services\AuthService;

class AccessController extends Controller
{

    private static $secretKey = "usuarios";

    public function __construct(
        private TokenGeneratorInterface $tokenGenerator,
        private DatabaseManager $databaseManager,
        private EmpleadoRepository $empleadoRepository,
        private LoginService $accessService
    ) {}

    public function login(Request $request, Response $res): Response
    {
        $data = $request->getParsedBody();
        $usuarioRequest = UsuarioRequest::fromArray($data);
        $data = $this->accessService->login($usuarioRequest);
        return JsonApiResponseHelper::respondWithResource($res, 'tokens', $data);
    }
}
