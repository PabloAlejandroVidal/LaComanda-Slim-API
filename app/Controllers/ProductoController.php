<?php
namespace App\Controllers;

use App\DTO\ProductoDTO;
use App\DTO\ProductoRequest;
use App\Http\JsonApiResponseHelper;
use App\Models\Sector;
use App\Services\ProductoService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as v;
use App\Models\Empleado;
use App\Models\Producto;

class ProductoController extends Controller
{    
    private static $criptoImgDir = './imagenes/productos/';
    public function __construct(private ProductoService $productoService) {
    }
    public function agregarProducto(Request $request, Response $res)
    {
        $data = $request->getParsedBody();
        $productoRequest = ProductoRequest::fromArray($data);
        $producto = $this->productoService->crearProducto($productoRequest);
        return JsonApiResponseHelper::respondWithCreatedResource($res, 'productos', $producto, '/productos/' . $producto['id']);
    }
    public function listarProductos(Request $request, Response $res)
    {
        $productos = $this->productoService->obtenerProductos();
        return JsonApiResponseHelper::respondWithCollection($res, 'productos',$productos);
    }
}
?>