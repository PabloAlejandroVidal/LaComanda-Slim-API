<?php
namespace App\Exceptions;

class ResourceNotCreatedException extends HttpBaseException
{
    public function __construct(string $recurso = "Recurso") {
        parent::__construct("No se pudo crear el $recurso", 400, "RESOURCE_NOT_CREATED");
    }
}
