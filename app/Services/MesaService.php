<?php
namespace App\Services;

use App\DTO\EmpleadoInput;
use App\DTO\EmpleadoRequest;
use App\DTO\MesaDTO;
use App\DTO\MesaRequest;
use App\Enums\EmpleadoType;
use App\Exceptions\ConflictException;
use App\Exceptions\EmpleadoNoCreadoException;
use App\Repositories\EmpleadoRepository;
use App\Repositories\PedidoRepository;
use App\Repositories\DetallePedidoRepository;
use App\Repositories\MesaRepository;
use App\Repositories\PermisoRepository;
use App\Repositories\TipoEmpleadoRepository;
use App\Services\AuthorizationService;
use App\Services\Utils;
use App\DTO\DetalleDTO;
use ArrayAccess;


class MesaService
{
    public function __construct(
        private PedidoRepository        $pedidoRepo,
        private DetallePedidoRepository $detalleRepo,
        private MesaRepository          $mesaRepo,
        private PermisoRepository       $permisoService,
        private EmpleadoRepository      $empleadoRepo,
        private TipoEmpleadoRepository  $tipoEmpleadoRepository,
    ) {}

    public function crearMesa(MesaRequest $mesaRequest): array {
        if ($this->mesaRepo->exists($mesaRequest->id)) {
            throw new ConflictException("El id ya está en uso, no se puede crear la mesa"); //209
        }
        $mesaId = $this->mesaRepo->add($mesaRequest->id);
        
        return [
            'id' => $mesaId,
            'estado' => 'libre'
        ];
    }

    public function getMesas(): array {
        $mesas = $this->mesaRepo->getMesas();
        $mesasLibres = [];
        $mesasOcupadas = [];

        foreach($mesas as $mesa){
            if($mesa['estado'] === "Cerrada"){
                $mesasLibres[] = new MesaDTO($mesa['id'], true);
            }else{
                $mesasOcupadas[] = new MesaDTO($mesa['id'], false);
            }
        }
        $output = [];
        if (count($mesasLibres)){
            $output[] = ["Detalle" => "Mesas Libres", "Mesas" => $mesasLibres];
        }
        if (count($mesasOcupadas)){
            $output[] = ["Detalle" => "Mesas Ocupadas", "Mesas" => $mesasOcupadas];
        }
        return $output;
    }
}
