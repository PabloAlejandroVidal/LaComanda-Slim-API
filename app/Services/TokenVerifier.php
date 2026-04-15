<?php
namespace App\Services;

use App\Interfaces\TokenVerifierInterface;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use App\Exceptions\UnauthorizedException;
use UnexpectedValueException;

class TokenVerifier implements TokenVerifierInterface
{
    public function __construct(
        private string $secretKey
    ) {}

    public function verifyToken(string $token): object
    {
        try {
            return JWT::decode(
                $token,
                new Key($this->secretKey, 'HS256')
            );
        } catch (ExpiredException $e) {
            throw new UnauthorizedException('Token expirado', previous: $e);
        } catch (SignatureInvalidException | BeforeValidException | UnexpectedValueException $e) {
            throw new UnauthorizedException('Token inválido', previous: $e);
        }
    }
}