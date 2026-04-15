<?php

use App\Controllers\AccessController;
use App\Controllers\EmpleadoController;
use App\Controllers\EmpleadoQueryController;
use App\Controllers\EncuestaController;
use App\Controllers\MesaController;
use App\Controllers\MesaQueryController;
use App\Controllers\PedidoController;
use App\Controllers\PedidoQueryController;
use App\Controllers\ProductoController;
use App\Controllers\ProductoQueryController;
use App\Domain\Empleado\EmpleadoType;
use App\Middlewares\TokenMiddleware;

return function ($app, $container) {
    $tokenMiddleware = $container->get(TokenMiddleware::class);

    // ===============================
    // PUBLIC
    // ===============================
    $app->post('/login', [AccessController::class, 'login']);

    // ===============================
    // EMPLEADOS
    // ===============================
    $app->get('/empleados', [EmpleadoController::class, 'listarEmpleados'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->post('/empleados', [EmpleadoController::class, 'agregarEmpleado'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->patch('/empleados/{id}/suspender', [EmpleadoController::class, 'suspenderEmpleado'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->patch('/empleados/{id}/reactivar', [EmpleadoController::class, 'reactivarEmpleado'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->delete('/empleados/{id}', [EmpleadoController::class, 'borrarEmpleado'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));



    // ===============================
    // EMPLEADOS - ESTADISTICAS
    // ===============================
    $app->get('/empleados/estadisticas', [EmpleadoQueryController::class, 'estadisticas'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));
        
    $app->get('/empleados/ingresos', [EmpleadoQueryController::class, 'ingresos'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    // ===============================
    // EMPLEADOS - OPERACIONES
    // ===============================
    $app->get('/empleados/operaciones/sector', [EmpleadoQueryController::class, 'getOperacionesSector'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->get('/empleados/operaciones/sector-empleado', [EmpleadoQueryController::class, 'getOperacionesSectorEmpleado'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->get('/empleados/operaciones', [EmpleadoQueryController::class, 'getOperacionesEmpleado'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));
        
    $app->get('/empleados/resumen/export/pdf', [EmpleadoQueryController::class, 'exportarPdfIngresos'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    // ===============================
    // EMPLEADOS - PRODUCCION
    // ===============================
    $app->get('/empleados/produccion', [EmpleadoQueryController::class, 'produccion'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->get('/empleados/produccion/sector', [EmpleadoQueryController::class, 'produccionPorSector'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->get('/empleados/produccion/sector-empleado', [EmpleadoQueryController::class, 'produccionPorSectorEmpleado'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));
        
    $app->get('/empleados/produccion/detallada', [EmpleadoQueryController::class, 'produccionDetallada'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));
        

    // ===============================
    // MESAS
    // ===============================
    $app->get('/mesas', [MesaQueryController::class, 'listarMesas']);
    
    $app->get('/mesas/por-estado', [MesaQueryController::class, 'listarMesasAgrupadasPorEstado']);

    $app->post('/mesas', [MesaController::class, 'agregarMesa'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO,
            EmpleadoType::MOZO
            ]));

    $app->get('/mesas/estadisticas/mas-usada', [MesaQueryController::class, 'masUsada'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
            ]));

    $app->get('/mesas/estadisticas/menos-usada', [MesaQueryController::class, 'menosUsada'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
            ]));

    $app->get('/mesas/estadisticas/mayor-facturacion', [MesaQueryController::class, 'mayorFacturacion'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
            ]));

    $app->get('/mesas/estadisticas/menor-facturacion', [MesaQueryController::class, 'menorFacturacion'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
            ]));

    $app->get('/mesas/estadisticas/mayor-importe', [MesaQueryController::class, 'mayorImporte'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
            ]));

    $app->get('/mesas/estadisticas/menor-importe', [MesaQueryController::class, 'menorImporte'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
            ]));

    $app->get('/mesas/estadisticas/facturacion', [MesaQueryController::class, 'facturacionEntre'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
            ]));

    $app->get('/mesas/estadisticas/comentarios/mejores', [MesaQueryController::class, 'mejoresComentarios'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
            ]));

    $app->get('/mesas/estadisticas/comentarios/peores', [MesaQueryController::class, 'peoresComentarios'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
            ]));

    // ===============================
    // PRODUCTOS
    // ===============================
    $app->get('/productos', [ProductoQueryController::class, 'listarProductos']);

    $app->post('/productos', [ProductoController::class, 'agregarProducto'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
            ]));

    $app->post('/productos/import/csv', [ProductoController::class, 'importarCsv'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->get('/productos/export/csv', [ProductoQueryController::class, 'exportarCsv'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->get('/productos/export/pdf', [ProductoQueryController::class, 'exportarPdf']);

    // ===============================
    // PEDIDOS - COMMANDS
    // ===============================
    $app->post('/pedidos', [PedidoController::class, 'crearPedido'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO, EmpleadoType::MOZO
        ]));

    $app->patch('/pedidos/{id}/iniciar-preparacion', [PedidoController::class, 'iniciarPreparacion'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO,
            EmpleadoType::CERVECERO,
            EmpleadoType::BARTENDER,
            EmpleadoType::COCINERO
        ]));

    $app->patch('/pedidos/{id}/marcar-listo', [PedidoController::class, 'marcarListo'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO,
            EmpleadoType::CERVECERO,
            EmpleadoType::BARTENDER,
            EmpleadoType::COCINERO
        ]));

    $app->patch('/pedidos/{id}/entregar', [PedidoController::class, 'entregar'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO,
            EmpleadoType::MOZO
        ]));

    $app->patch('/pedidos/{id}/cobrar', [PedidoController::class, 'cobrarMesa'])->add($tokenMiddleware([
            EmpleadoType::SOCIO,
            EmpleadoType::MOZO
        ]));

    $app->patch('/pedidos/{id}/cerrar', [PedidoController::class, 'cerrar'])->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->patch('/pedidos/{id}/cancelar', [PedidoController::class, 'cancelar'])
    ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->post('/pedidos/{pedidoId}/encuesta', [EncuestaController::class, 'crear']);

    $app->post('/pedidos/{id}/foto', [PedidoController::class, 'subirFoto'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO,
            EmpleadoType::CERVECERO,
            EmpleadoType::BARTENDER,
            EmpleadoType::COCINERO
        ]));

    // ===============================
    // PEDIDOS - QUERIES
    // ===============================
    $app->get('/pedidos', [PedidoQueryController::class, 'listar'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO,
            EmpleadoType::MOZO,
            EmpleadoType::CERVECERO,
            EmpleadoType::BARTENDER,
            EmpleadoType::COCINERO
        ]));

    $app->get('/pedidos/detalles/pendientes', [PedidoQueryController::class, 'pendientesDelSector'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO,
            EmpleadoType::COCINERO,
            EmpleadoType::BARTENDER,
            EmpleadoType::CERVECERO,
        ]));

    $app->get('/pedidos/detalles/en-preparacion', [PedidoQueryController::class, 'enPreparacionDelSector'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO,
            EmpleadoType::COCINERO,
            EmpleadoType::BARTENDER,
            EmpleadoType::CERVECERO,
        ]));

    $app->get('/pedidos/estadisticas/mas-vendidos', [PedidoQueryController::class, 'masVendidos'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->get('/pedidos/estadisticas/menos-vendidos', [PedidoQueryController::class, 'menosVendidos'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->get('/pedidos/estadisticas/fuera-de-tiempo', [PedidoQueryController::class, 'noEntregadosEnTiempo'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->get('/pedidos/estadisticas/cancelados', [PedidoQueryController::class, 'cancelados'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->get('/pedidos/estadisticas/cerrados', [PedidoQueryController::class, 'cerrados'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO
        ]));

    $app->get('/pedidos/{id}', [PedidoQueryController::class, 'ver'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO,
            EmpleadoType::MOZO,
            EmpleadoType::CERVECERO,
            EmpleadoType::BARTENDER,
            EmpleadoType::COCINERO
        ]));
        
    $app->get('/pedidos/{id}/detalles', [PedidoQueryController::class, 'verDetalles'])
        ->add($tokenMiddleware([
            EmpleadoType::SOCIO,
            EmpleadoType::MOZO,
            EmpleadoType::CERVECERO,
            EmpleadoType::BARTENDER,
            EmpleadoType::COCINERO
        ]));

    $app->get('/pedidos/seguimiento/{mesaId}/{pedidoId}', [PedidoQueryController::class, 'seguimiento']);
};