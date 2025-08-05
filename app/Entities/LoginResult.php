<?php
namespace App\DTO;

class LoginResult {
    public function __construct(
        public bool $succes,
        public string $token,
    ) {}
}

?>
