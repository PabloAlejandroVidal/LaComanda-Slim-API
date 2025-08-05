<?php
namespace App\Services;

use App\Interfaces\TokenVerifierInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use League\Csv\Exception;

class TokenVerifier implements TokenVerifierInterface
{
    private $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function verifyToken(string $token)
    {        
        try {
            $decodedToken = JWT::decode($token, $this->secretKey, ['HS256']);
            $expiracion = $decodedToken->exp ?? null;
            if ($expiracion && time() > $expiracion) {
                throw new ExpiredException('Sesión expirada');
            }
        } catch (SignatureInvalidException $e) {
            throw new SignatureInvalidException('Token no válido');
        } catch (\Throwable $e) {
            throw new \RuntimeException("error al validar el token, mensaje: " . $e->getMessage());
        }
        return $decodedToken;
    }
}

?>