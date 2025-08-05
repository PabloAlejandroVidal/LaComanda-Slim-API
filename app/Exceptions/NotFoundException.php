<?php
namespace App\Exceptions;

class NotFoundException extends HttpBaseException
{
    public function __construct(string $recurso = "Recurso") {
        parent::__construct("$recurso no encontrado", 404, "RESOURCE_NOT_FOUND");
    }
}
