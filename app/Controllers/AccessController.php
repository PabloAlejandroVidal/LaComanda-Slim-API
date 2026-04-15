<?php

namespace App\Controllers;

use App\DTO\Request\UsuarioRequest;
use App\Services\LoginService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AccessController extends BaseController
{

    public function __construct(
        private LoginService $loginService
    ) {}
    public function login(Request $request, Response $response): Response
    {
        $usuarioRequest = $this->getJsonDtoOrFail($request, UsuarioRequest::class);
        $data = $this->loginService->login($usuarioRequest);

        return $this->ok($response, $data, 'Login correcto');
    }
}
