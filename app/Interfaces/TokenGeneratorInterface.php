<?php
namespace App\Interfaces;
interface TokenGeneratorInterface
{
    public function generateToken(array | object $token): string;
}

?>