<?php
namespace App\Services;

use App\Repositories\MesaRepository;


final class MesaQueryService
{
    public function __construct(
        private MesaRepository $mesaRepo
    ) {}

    public function masUsada(string $from, string $to): ?array
    {
        return $this->mesaRepo->getMesaMasUsada($from, $to);
    }

    public function masFacturo(string $from, string $to): ?array
    {
        return $this->mesaRepo->getMesaQueMasFacturo($from, $to);
    }

    public function mayorFactura(string $from, string $to): ?array
    {
        return $this->mesaRepo->getMayorFactura($from, $to);
    }
}
