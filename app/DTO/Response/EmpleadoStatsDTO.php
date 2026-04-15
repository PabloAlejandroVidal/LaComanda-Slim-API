<?php
namespace App\DTO\Response;

final class EmpleadoStatsDTO implements \JsonSerializable
{
    public function __construct(
        public int $empleadoId,
        public string $nombre,
        public string $email,
        public string $tipo,
        public int $tomasPedido,
        public int $asignaciones,
        public int $preparaciones,
        public int $entregas,
        public int $cobros,
        public int $cierres,
        public int $cancelaciones,
        public int $cantidadIngresos = 0,
        public ?string $primerIngreso = null,
        public ?string $ultimoIngreso = null,
    ) {}

    public function getTotalOperaciones(): int
    {
        return
            $this->tomasPedido +
            $this->asignaciones +
            $this->preparaciones +
            $this->entregas +
            $this->cobros +
            $this->cierres +
            $this->cancelaciones;
    }

    public function jsonSerialize(): array
    {
        return [
            'empleadoId' => $this->empleadoId,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'tipo' => $this->tipo,
            'tomasPedido' => $this->tomasPedido,
            'asignaciones' => $this->asignaciones,
            'preparaciones' => $this->preparaciones,
            'entregas' => $this->entregas,
            'cobros' => $this->cobros,
            'cierres' => $this->cierres,
            'cancelaciones' => $this->cancelaciones,
            'totalOperaciones' => $this->getTotalOperaciones(),
            'cantidadIngresos' => $this->cantidadIngresos,
            'primerIngreso' => $this->primerIngreso,
            'ultimoIngreso' => $this->ultimoIngreso,
        ];
    }
}