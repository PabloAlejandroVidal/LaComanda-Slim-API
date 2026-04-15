<?php
namespace App\Services;

use App\Entities\TokenPayload;
use App\Interfaces\TokenGeneratorInterface;
use Firebase\JWT\JWT;

class TokenGenerator implements TokenGeneratorInterface
{
    public function __construct(
        private string $secretKey,
        private int $expiration
    ) {}

    public function generateToken(TokenPayload $payload): string
    {
        $now = time();

        $jwtPayload = [
            'iat' => $now,
            'exp' => $now + $this->expiration,
            'data' => [
                'id' => $payload->id,
                'nombre' => $payload->nombre,
                'email' => $payload->email,
                'rol' => $payload->rol,
            ]
        ];

        return JWT::encode($jwtPayload, $this->secretKey, 'HS256');
    }
}