<?php
namespace App\DTO\Response;

class LoginResponseDTO implements \JsonSerializable
{
    public function __construct(
        readonly public string $accessToken,
        readonly public string $tokenType,
        readonly public int $expiresIn,
        readonly public AuthenticatedUserDTO $user,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
            'user' => $this->user,
        ];
    }
}