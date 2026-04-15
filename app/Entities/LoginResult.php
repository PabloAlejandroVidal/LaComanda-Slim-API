<?php
namespace App\Entities;

class LoginResult {
    public function __construct(
        public bool $succes,
        public string $token,
    ) {}
}

?>
