<?php
namespace App\Interfaces;
interface TokenVerifierInterface
{
    public function verifyToken(string $token);
}

?>