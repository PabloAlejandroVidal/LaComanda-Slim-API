<?php

namespace App\Services;

use App\DTO\Response\MesaDTO;
use App\Domain\Mesa\EstadoMesa;
use App\Repositories\MesaRepository;

final class MesaQueryService
{
    public function __construct(
        private MesaRepository $mesaRepo
    ) {}

    /*-------------------------------------------------
    | Listar mesas agrupadas por estado
    -------------------------------------------------*/
    public function listarMesas(): array
    {
        $mesas = $this->mesaRepo->getMesas();

        $output = [];

        foreach ($mesas as $mesa) {
            $output[] = [
                'id' => $mesa['id'],
                'estado' => $mesa['estado']->value,
                'descripcion' => $mesa['estado_descripcion'],
                'libre' => $mesa['estado'] === EstadoMesa::CERRADA,
            ];
        }

        return $output;
    }
    
    public function listarMesasAgrupadasPorEstado(): array
    {
        $mesas = $this->mesaRepo->getMesas();

        $grupos = [
            EstadoMesa::CERRADA->value => [
                'estado' => EstadoMesa::CERRADA->value,
                'descripcion' => null,
                'mesas' => [],
            ],
            EstadoMesa::ESPERANDO_PEDIDO->value => [
                'estado' => EstadoMesa::ESPERANDO_PEDIDO->value,
                'descripcion' => null,
                'mesas' => [],
            ],
            EstadoMesa::COMIENDO->value => [
                'estado' => EstadoMesa::COMIENDO->value,
                'descripcion' => null,
                'mesas' => [],
            ],
            EstadoMesa::PAGANDO->value => [
                'estado' => EstadoMesa::PAGANDO->value,
                'descripcion' => null,
                'mesas' => [],
            ],
        ];

        foreach ($mesas as $mesa) {
            $claveEstado = $mesa['estado']->value;

            if ($grupos[$claveEstado]['descripcion'] === null) {
                $grupos[$claveEstado]['descripcion'] = $mesa['estado_descripcion'];
            }

            $grupos[$claveEstado]['mesas'][] = [
                'id' => $mesa['id'],
                'libre' => $mesa['estado'] === EstadoMesa::CERRADA,
            ];
        }

        return array_values(
            array_filter(
                $grupos,
                fn(array $grupo) => !empty($grupo['mesas'])
            )
        );
    }

    public function masUsada(string $from, string $to): ?array
    {
        return $this->mesaRepo->getMesaMasUsada($from, $to);
    }

    public function menosUsada(string $from, string $to): ?array
    {
        return $this->mesaRepo->getMesaMenosUsada($from, $to);
    }

    public function mayorFacturacion(string $from, string $to): ?array
    {
        return $this->mesaRepo->getMesaMayorFacturacion($from, $to);
    }

    public function menorFacturacion(string $from, string $to): ?array
    {
        return $this->mesaRepo->getMesaMenorFacturacion($from, $to);
    }

    public function mayorImporte(string $from, string $to): ?array
    {
        return $this->mesaRepo->getMayorFactura($from, $to);
    }

    public function menorImporte(string $from, string $to): ?array
    {
        return $this->mesaRepo->getMenorFactura($from, $to);
    }

    public function facturacionEntre(string $from, string $to): ?array
    {
        return $this->mesaRepo->getFacturacionEntre($from, $to);
    }

    public function mejoresComentarios(string $from, string $to): ?array
    {
        return $this->mesaRepo->getMejoresComentarios($from, $to);
    }

    public function peoresComentarios(string $from, string $to): ?array
    {
        return $this->mesaRepo->getPeoresComentarios($from, $to);
    }
}