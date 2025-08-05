<?php
namespace App\Controllers;

use App\Repositories\DetallePedidoRepository;
use App\Repositories\PedidoRepository;
use App\DTO\DetalleDTO;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Repositories\EmpleadoRepository;
use App\Repositories\MesaRepository;
use App\Services\ComandaService;
use App\Services\PedidoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as v;
use App\Databases\DatabaseManager;

class InformeController extends Controller
{    
    private static $criptoImgDir = './imagenes/productos/';
    private DatabaseManager $db;


    public function __construct(
        private PedidoService $pedidoService,
        private MesaRepository $mesaRepo,
        private EmpleadoRepository $empleadoRepo,
        private PedidoRepository $pedidoRepo,
        private DetallePedidoRepository $detalleRepo,
    ) {}

    public function getOperacionesSector(Request $req, Response $res) {}
    public function getOperacionesSectorEmpleado(Request $req, Response $res) {}
    public function getOperacionesEmpleado(Request $req, Response $res) {}
    public function getPedidoMasVendido(Request $req, Response $res) {}
    public function getPedidoMenosVendido(Request $req, Response $res) {}
    public function verPedidosEntregadosTarde(Request $req, Response $res) {}
    public function verPedidosCancelados(Request $req, Response $res) {}
    public function getMesaMasUsada(Request $req, Response $res) {}
    public function getMesaMenosUsada(Request $req, Response $res) {}
    public function getMesaMayorFacturacion(Request $req, Response $res) {}
    public function getMesaMenorFacturacion(Request $req, Response $res) {}
    public function getMesaMayorImporte(Request $req, Response $res) {}
    public function getMesaMenorImporte(Request $req, Response $res) {}
    public function getMesaFacturacionEntre(Request $req, Response $res) {}
    public function getMesaMejoresComentarios(Request $req, Response $res) {}
    public function getMesaPeoresComentarios(Request $req, Response $res) {}
}
?>