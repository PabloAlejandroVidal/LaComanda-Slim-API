<?php
namespace App\DTO\Response;

final class EmpleadoProduccionDTO implements \JsonSerializable
{
    public function __construct(
        public int $empleadoId,
        public string $nombre,
        public string $email,
        public string $tipo,
        public int $detallesPreparados,
        public int $unidadesPreparadas,
        public int $pedidosIntervenidos
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'empleadoId' => $this->empleadoId,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'tipo' => $this->tipo,
            'detallesPreparados' => $this->detallesPreparados,
            'unidadesPreparadas' => $this->unidadesPreparadas,
            'pedidosIntervenidos' => $this->pedidosIntervenidos,
        ];
    }
}