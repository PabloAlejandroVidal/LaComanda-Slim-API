<?php
namespace App\Interfaces;

use App\Entities\TokenPayload;
interface TokenGeneratorInterface
{
    public function generateToken(TokenPayload $payload): string;
}

?>