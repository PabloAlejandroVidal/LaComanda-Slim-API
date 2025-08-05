<?php
namespace App\Controllers;

use App\Repositories\{
    DetallePedidoRepository,
    PedidoRepository,
    EmpleadoRepository,
    MesaRepository,
    EstadoDetalle
};
use App\Services\{
    ComandaService,
    PedidoService
};
use App\DTO\DetalleDTO;
use App\Utils;
use Psr\Http\Message\{
    ResponseInterface as Response,
    ServerRequestInterface as Request
};
use Respect\Validation\{
    Validator as v,
    Exceptions\NestedValidationException
};

class PedidoController extends Controller
{
    public function __construct(
        private PedidoService $pedidoService,
        private MesaRepository $mesaRepo,
        private EmpleadoRepository $empleadoRepo,
        private PedidoRepository $pedidoRepo,
        private DetallePedidoRepository $detalleRepo,
    ) {}

   public function crearPedido(Request $req, Response $res): Response
{
    try {
        $data = json_decode($req->getBody()->getContents(), true) ?? [];

        v::key('nombre', v::alpha()->length(2, 40))
          ->key('mesa', v::alnum()->length(5, 5))
          ->key('detalles', v::arrayType()->each(
                v::key('id', v::intVal())
                 ->key('cantidad', v::intVal()->positive())
          ))->assert($data);

        $token = $req->getAttribute('decodedToken');

        $id = $this->pedidoService->crearPedido(
            mesaId: strtoupper($data['mesa']),
            mozoEmail: $token->email,
            nombreCliente: $data['nombre'],
            detalles: $data['detalles']
        );

        return $this->respond($res, 201, 'Pedido tomado con éxito', ['id' => $id]);

    } catch (\DomainException $e) {
        return $this->respond($res, 422, $e->getMessage());
    } catch (\Throwable $e) {
        return $this->respond($res, 500, 'Error interno: ' . $e->getMessage());
    }
}

    
    public function agregarDetalles(Request $req, Response $res): Response
    {
        try {
            $pedidoId = $req->getAttribute('route')->getArgument('id');
            $data = json_decode($req->getBody()->getContents(), true) ?? [];

            $this->validate($data, v::arrayType()->each(
                v::key('id', v::intVal())
                ->key('cantidad', v::intVal()->positive())
            ));

            $this->detalleRepo->insertarDetalles($pedidoId, $data);

            return $this->respond($res, 200, 'Detalles agregados correctamente');

        } catch (\DomainException $e) {
            return $this->respond($res, 422, $e->getMessage());
        } catch (\Throwable $e) {
            return $this->respond($res, 500, 'Error interno: ' . $e->getMessage());
        }
    }


    // mostrar detalles de pedidos por una mesa y pedido puntual, agrupados por sin asignar, asignados, por entregar y entregados
public function verDetalles(Request $req, Response $res): Response
{
    try {
        $pedidoId = $req->getAttribute('route')->getArgument('pedidoId');
        $mesaId   = $req->getAttribute('route')->getArgument('mesaId');

        // Validar existencia del pedido y su relación con la mesa
        $pedido = $this->pedidoRepo->getPedidoById($pedidoId);

        if (!$pedido || $pedido['mesa_id'] !== $mesaId) {
            return $this->respond($res, 404, "No se encontró el pedido $pedidoId para la mesa $mesaId");
        }
        
        // Obtener detalles del pedido
        $detalles = $this->detalleRepo->getDetallesDelPedido($pedidoId);
        
        if (empty($detalles)) {
            return $this->respond($res, 200, "El pedido $pedidoId no tiene productos cargados");
        }
        
        // Agrupar por sector
        $agrupados = [
            'mesa_id' => $mesaId,
            'pedido_id' => $pedidoId,
            'productos_por_sector' => []
        ];

        foreach ($detalles as $item) {
            $sector = $item['sector_nombre'];
            $agrupados['productos_por_sector'][$sector][] = [
                'producto' => $item['producto_nombre'],
                'cantidad' => $item['cantidad']
            ];
        }
        
        return $this->respond($res, 200, "Productos del pedido $pedidoId agrupados por sector", $agrupados);
        
    } catch (\Throwable $e) {
        return $this->respond($res, 500, $e->getMessage());
    }
}public function cerrarPedido(Request $req, Response $res): Response
{
    try {
        $pedidoId = $req->getAttribute('route')->getArgument('pedidoId');
        $mesaId   = $req->getAttribute('route')->getArgument('mesaId');

        // Validar existencia del pedido y su relación con la mesa
        $pedido = $this->pedidoRepo->getPedidoById($pedidoId);

        if (!$pedido || $pedido['mesa_id'] !== $mesaId) {
            return $this->respond($res, 404, "No se encontró el pedido $pedidoId para la mesa $mesaId");
        }
        $token = $req->getAttribute('decodedToken');
        $empleado = $this->empleadoRepo->getEmpleadoByEmail($token->email);
        if (!$empleado) return $this->respond($res, 404, 'Empleado no encontrado');

        $this->pedidoService->cerrarComanda($pedidoId, $empleado['id']);
        
        return $this->respond($res, 200, "Pedido cerrado exitosamente");
        
    } catch (\Throwable $e) {
        return $this->respond($res, 500, $e->getMessage());
    }
}



    public function asignarPedido(Request $req, Response $res): Response
    {
        return $this->procesarAccionSector($req, $res, 'asignar');
    }

    public function prepararPedido(Request $req, Response $res): Response
    {
        return $this->procesarAccionSector($req, $res, 'preparar');
    }

    public function entregarPedido(Request $req, Response $res): Response
    {
        return $this->procesarAccionSector($req, $res, 'entregar');
    }

    private function procesarAccionSector(Request $req, Response $res, string $accion): Response
    {
        try {
            $data = json_decode($req->getBody()->getContents(), true) ?? [];
            
            $validator = v::key('pedido', v::alnum()->length(5, 5))
                        ->key('mesa', v::alnum()->length(5, 5));
            $validator->assert($data);
            
            $token = $req->getAttribute('decodedToken');
            $empleadoId = $token->id;

            $empleado = $this->empleadoRepo->getEmpleadoById($empleadoId);
            if (!$empleado || !isset($empleado['sector_id'])) {
                throw new \DomainException('Empleado o sector no válido');
            }
            $this->pedidoService->procesarPedido( $data['pedido'],$data['mesa'], $empleadoId, $empleado['sector_id'], $accion);
            return $this->respond($res,200, ucfirst($accion) . ' registrada correctamente');
        } 
        catch (NestedValidationException $e) {
            return $this->handleValidationErrors($res, $e);
        }
        catch (\DomainException $e) {
            return $this->respond($res, 422, "Empleado o Sector no válido", null, $e->getMessage());
        }
        catch (\Throwable $e) {
            return $this->respondWithJson($res, [
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    
    //muestra pedidos pendientes agrupados
    public function verPedidosPendientes(Request $request, Response $response): Response
    {
        try {
            $token = $request->getAttribute('decodedToken');
            $empleadoId = $token->id;

            $sectoresClaveId = $this->empleadoRepo->getSectoresByEmpleado($empleadoId);
            if (empty($sectoresClaveId)) {
                return $this->respondWithJson($response, [
                    'status' => 'ERROR',
                    'message' => 'No hay pedidos pendientes'
                ], 403);
            }
            $mesas = [];
            
            foreach ($sectoresClaveId as $sector) {
                $detalles = $this->detalleRepo->getPedidosDetalles($sector['id'], DetallePedidoRepository::SIN_ASIGNAR);
                print_r($sector);

                foreach ($detalles as $detalle) {
                    $mesaId   = $detalle['mesa_id'];
                    $pedidoId = $detalle['pedido_id'];
                    $sectorNombre = $detalle['sector_nombre'];

                    // Clave única por mesa y pedido
                    $key = $mesaId . '|' . $pedidoId;

                    // Si no existe aún, inicializamos
                    if (!isset($mesas[$key])) {
                        $mesas[$key] = [
                            'mesa_id' => $mesaId,
                            'pedido_id' => $pedidoId,
                            'productos_por_sector' => []
                        ];
                    }

                    // Agrupar por sector
                    $mesas[$key]['productos_por_sector'][$sectorNombre][] = [
                        'producto' => $detalle['producto_nombre'],
                        'cantidad' => $detalle['cantidad']
                    ];
                }
            }

            // Reindexado numerico del array
            $mesas = array_values($mesas);

            return $this->respondWithJson($response, [
                'status' => 'OK',
                'message' => 'Pedidos pendientes para preparar',
                'data' => array_values($mesas),
            ], 200);

        } catch (\Throwable $e) {
            return $this->respondWithJson($response, [
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    //quien se asigno los podidos puede ver los pedidos que tiene en preparacion agrupados por mesa/pedido y subagrupados en sectores (normalmente vera solo un sector)
    public function verPedidosPreparacion(Request $req, Response $res): Response
    {
        try {
            $token = $this->getToken($req);
            $empleado = $this->empleadoRepo->getEmpleadoById($token->id);

            if (!$empleado || !isset($empleado['sector_id'])) {
                return $this->respond($res, 400, 'Sector no asignado al empleado');
            }
            
            $result = $this->pedidoService->prepararLote($empleado['sector_id']);
            
            return $this->respond($res, 200, 'Pedidos en preparación', $result);
        } catch (\Throwable $e) {
            return $this->respond($res, 500, $e->getMessage());
        }
    }
}
