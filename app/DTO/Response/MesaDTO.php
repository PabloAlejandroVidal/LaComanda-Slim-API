<?php
namespace App\DTO;

class MesaDTO {
    public function __construct(
        public string $id,
        public ?bool $libre = null // puede ser null si no calculás el estado en ese momento
    ) {}
}
?>
