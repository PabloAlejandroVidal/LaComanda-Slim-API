<?php
use App\Controllers\AccessController;
use App\Controllers\EmpleadoController;
use App\Controllers\InformeController;
use App\Controllers\MesaController;
use App\Controllers\PedidoController;
use App\Controllers\ProductoController;
use App\Middlewares\TokenMiddleware;
use App\Enums\EmpleadoType;

$tokenMiddleware = $container->get(TokenMiddleware::class);

// Public
$app->get('/access', [AccessController::class, 'access']);
$app->post('/login', [AccessController::class, 'login']);

// Empleados
$app->get('/empleados', [EmpleadoController::class, 'listarEmpleados'])->add($tokenMiddleware([EmpleadoType::SOCIO]));
$app->post('/empleados', [EmpleadoController::class, 'agregarEmpleado'])->add($tokenMiddleware([EmpleadoType::SOCIO]));

// Mesas
$app->get('/mesas', [MesaController::class, 'listarMesas']);
$app->post('/mesas', [MesaController::class, 'agregarMesa'])->add($tokenMiddleware([EmpleadoType::SOCIO, EmpleadoType::MOZO]));

// Productos
$app->get('/productos', [ProductoController::class, 'listarProductos']);
$app->post('/productos', [ProductoController::class, 'agregarProducto'])->add($tokenMiddleware([EmpleadoType::SOCIO]));

// Pedidos por mesa "ABM"
$app->post('/pedidos', [PedidoController::class, 'crearPedido'])->add($tokenMiddleware([EmpleadoType::SOCIO, EmpleadoType::MOZO]));
$app->post('/pedidos/{id}/detalles', [PedidoController::class, 'agregarDetalles'])->add($tokenMiddleware([EmpleadoType::SOCIO, EmpleadoType::MOZO]));
$app->get('/pedidos/detalles', [PedidoController::class, 'verDetalles'])->add($tokenMiddleware([EmpleadoType::SOCIO, EmpleadoType::CERVECERO, EmpleadoType::BARTENDER, EmpleadoType::COCINERO]));

//se asigna quien se encargara de la preparacion, el detalle del pedido se marca como asignado/ en preparacion
//se indica el pedido o mesa, el sector y se asigna los productos como en preparacion en lote
$app->patch('/pedidos/detalles/asignar', [PedidoController::class, 'asignarPedido'])->add($tokenMiddleware([EmpleadoType::SOCIO, EmpleadoType::CERVECERO, EmpleadoType::BARTENDER, EmpleadoType::COCINERO]));

//se indica el pedido o mesa, el sector y se indica los productos en preparacion como preparados
//solo se prepararan los pedidos que se tiene marcados en preparacion
$app->patch('/pedidos/detalles/preparar', [PedidoController::class, 'prepararPedido'])->add($tokenMiddleware([EmpleadoType::SOCIO, EmpleadoType::CERVECERO, EmpleadoType::BARTENDER, EmpleadoType::COCINERO]));

//se indica el pedido o mesa, el sector y se marcan los productos preparados como en entregados
$app->patch('/pedidos/detalles/entregar', [PedidoController::class, 'entregarPedido'])->add($tokenMiddleware([EmpleadoType::SOCIO, EmpleadoType::MOZO]));

//Para todas las mesas
//muestra pedidos pendientes, agrupados por mesa y subagrupados por sector
$app->get('/pedidos/detalles/pendientes', [PedidoController::class, 'verPedidosPendientes'])->add($tokenMiddleware([EmpleadoType::SOCIO, EmpleadoType::CERVECERO, EmpleadoType::BARTENDER, EmpleadoType::COCINERO]));

//quien se asigno a un lote de pedidos puede ver, agrupados por mesa y subagrupados por sector
$app->get('/pedidos/detalles/preparando', [PedidoController::class, 'verPedidosPreparacion'])->add($tokenMiddleware([EmpleadoType::SOCIO, EmpleadoType::CERVECERO, EmpleadoType::BARTENDER, EmpleadoType::COCINERO]));

$app->get('/pedidos/cerrar', [PedidoController::class, 'cerrarPedido'])->add($tokenMiddleware([EmpleadoType::SOCIO, EmpleadoType::CERVECERO, EmpleadoType::BARTENDER, EmpleadoType::COCINERO]));


// Informes
$app->get('/informes/empleados/ingresos', [InformeController::class, 'verEmpleadosIngreso'])->add($tokenMiddleware([EmpleadoType::SOCIO]));
$app->get('/informes/operaciones/sector', [InformeController::class, 'getOperacionesSector'])->add($tokenMiddleware([EmpleadoType::SOCIO]));
$app->get('/informes/operaciones/sector-empleado', [InformeController::class, 'getOperacionesSectorEmpleado'])->add($tokenMiddleware([EmpleadoType::SOCIO]));
$app->get('/informes/operaciones/empleado', [InformeController::class, 'getOperacionesEmpleado'])->add($tokenMiddleware([EmpleadoType::SOCIO]));

$app->get('/informes/pedidos/mas-vendido', [InformeController::class, 'getPedidoMasVendido'])->add($tokenMiddleware([EmpleadoType::SOCIO]));
$app->get('/informes/pedidos/menos-vendido', [InformeController::class, 'getPedidoMenosVendido'])->add($tokenMiddleware([EmpleadoType::SOCIO]));
$app->get('/informes/pedidos/entregados-tarde', [InformeController::class, 'verPedidosEntregadosTarde'])->add($tokenMiddleware([EmpleadoType::SOCIO]));
$app->get('/informes/pedidos/cancelados', [InformeController::class, 'verPedidosCancelados'])->add($tokenMiddleware([EmpleadoType::SOCIO]));

$app->get('/informes/mesas/mas-usada', [InformeController::class, 'getMesaMasUsada'])->add($tokenMiddleware([EmpleadoType::SOCIO]));
$app->get('/informes/mesas/menos-usada', [InformeController::class, 'getMesaMenosUsada'])->add($tokenMiddleware([EmpleadoType::SOCIO]));
$app->get('/informes/mesas/mayor-facturacion', [InformeController::class, 'getMesaMayorFacturacion'])->add($tokenMiddleware([EmpleadoType::SOCIO]));
$app->get('/informes/mesas/menor-facturacion', [InformeController::class, 'getMesaMenorFacturacion'])->add($tokenMiddleware([EmpleadoType::SOCIO]));
$app->get('/informes/mesas/mayor-importe', [InformeController::class, 'getMesaMayorImporte'])->add($tokenMiddleware([EmpleadoType::SOCIO]));
$app->get('/informes/mesas/menor-importe', [InformeController::class, 'getMesaMenorImporte'])->add($tokenMiddleware([EmpleadoType::SOCIO]));
$app->get('/informes/mesas/facturacion', [InformeController::class, 'getMesaFacturacionEntre'])->add($tokenMiddleware([EmpleadoType::SOCIO]));
$app->get('/informes/mesas/comentarios/mejores', [InformeController::class, 'getMesaMejoresComentarios'])->add($tokenMiddleware([EmpleadoType::SOCIO]));
$app->get('/informes/mesas/comentarios/peores', [InformeController::class, 'getMesaPeoresComentarios'])->add($tokenMiddleware([EmpleadoType::SOCIO]));
