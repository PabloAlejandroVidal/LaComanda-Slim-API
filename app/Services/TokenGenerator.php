<?php
namespace App\Services;

use App\Interfaces\TokenGeneratorInterface;
use Firebase\JWT\JWT;

class TokenGenerator implements TokenGeneratorInterface
{
    private $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function generateToken(array | object $payload)
    {
        $token = JWT::encode($payload, $this->secretKey, "HS256");
        return $token;
    }
}

?>