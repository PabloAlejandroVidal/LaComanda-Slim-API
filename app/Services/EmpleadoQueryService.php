<?php

namespace App\Services;

use App\DTO\Response\EmpleadoStatsDTO;
use App\Exceptions\ResourceNotCreatedException;
use App\Repositories\EmpleadoOperacionRepository;
use App\Repositories\EmpleadoProduccionRepository;
use App\Repositories\EmpleadoRepository;
use Dompdf\Dompdf;
use Dompdf\Options;

final class EmpleadoQueryService
{
    public function __construct(
        private EmpleadoRepository $empleadoRepo,
        private EmpleadoOperacionRepository $empleadoOperacionRepo,
        private EmpleadoProduccionRepository $empleadoProduccionRepo
    ) {}

    /**
     * @return EmpleadoStatsDTO[]
     */
    public function estadisticas(string $from, string $to): array
    {
        $rows = $this->empleadoOperacionRepo->getEstadisticasPorEmpleado($from, $to);
        $resumenIngresos = $this->empleadoRepo->getResumenIngresosPorEmpleado($from, $to);

        $ingresosPorEmpleado = [];

        foreach ($resumenIngresos as $row) {
            $ingresosPorEmpleado[(int) $row['empleado_id']] = $row;
        }

        $result = [];

        foreach ($rows as $row) {
            $empleadoId = (int) $row['id'];
            $ingresos = $ingresosPorEmpleado[$empleadoId] ?? null;

            $result[] = new EmpleadoStatsDTO(
                empleadoId: $empleadoId,
                nombre: $row['nombre'],
                email: $row['email'],
                tipo: $row['tipo'],
                tomasPedido: (int) ($row['tomas_pedido'] ?? 0),
                asignaciones: (int) ($row['asignaciones'] ?? 0),
                preparaciones: (int) ($row['preparaciones'] ?? 0),
                entregas: (int) ($row['entregas'] ?? 0),
                cobros: (int) ($row['cobros'] ?? 0),
                cierres: (int) ($row['cierres'] ?? 0),
                cancelaciones: (int) ($row['cancelaciones'] ?? 0),
                cantidadIngresos: (int) ($ingresos['cantidad_ingresos'] ?? 0),
                primerIngreso: $ingresos['primer_ingreso'] ?? null,
                ultimoIngreso: $ingresos['ultimo_ingreso'] ?? null,
            );
        }

        return $result;
    }

    public function obtenerOperacionesSector(string $from, string $to): array
    {
        return $this->empleadoOperacionRepo->getOperacionesPorSector($from, $to);
    }

    public function obtenerOperacionesSectorEmpleado(string $from, string $to): array
    {
        return $this->empleadoOperacionRepo->getOperacionesPorSectorYEmpleado($from, $to);
    }

    public function obtenerOperacionesEmpleado(string $from, string $to): array
    {
        return $this->empleadoOperacionRepo->getOperacionesPorEmpleado($from, $to);
    }

    public function obtenerProduccion(string $from, string $to): array
    {
        return $this->empleadoProduccionRepo->getProduccionPorEmpleado($from, $to);
    }

    public function obtenerProduccionPorSector(string $from, string $to): array
    {
        return $this->empleadoProduccionRepo->getProduccionPorSector($from, $to);
    }

    public function obtenerProduccionPorSectorEmpleado(string $from, string $to): array
    {
        return $this->empleadoProduccionRepo->getProduccionPorSectorYEmpleado($from, $to);
    }

    public function obtenerProduccionDetallada(string $from, string $to): array
    {
        return $this->empleadoProduccionRepo->getProduccionDetalladaPorEmpleado($from, $to);
    }

    public function obtenerIngresos(string $from, string $to): array
    {
        return $this->empleadoRepo->getIngresosPorPeriodo($from, $to);
    }
    public function exportarPdfIngresos(): string
    {
        $empleados = $this->empleadoRepo->getResumenIngresosParaPdf();
        $html = $this->buildIngresosPdfHtml($empleados);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $pdf = $dompdf->output();

        if ($pdf === '') {
            throw new ResourceNotCreatedException(
                'No se pudo generar el archivo PDF de ingresos de empleados'
            );
        }

        return $pdf;
    }

    private function buildIngresosPdfHtml(array $empleados): string
    {
        $fecha = date('d/m/Y H:i');
        $rows = '';

        foreach ($empleados as $empleado) {
            $nombre = htmlspecialchars((string) $empleado['nombre'], ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars((string) $empleado['email'], ENT_QUOTES, 'UTF-8');
            $tipo = htmlspecialchars((string) $empleado['tipo'], ENT_QUOTES, 'UTF-8');
            $cantidadIngresos = (int) ($empleado['cantidad_ingresos'] ?? 0);
            $primerIngreso = $empleado['primer_ingreso'] ?? '-';
            $ultimoIngreso = $empleado['ultimo_ingreso'] ?? '-';
            $totalOperaciones = (int) ($empleado['total_operaciones'] ?? 0);

            $rows .= "
                <tr>
                    <td>{$nombre}</td>
                    <td>{$email}</td>
                    <td>{$tipo}</td>
                    <td class='num'>{$cantidadIngresos}</td>
                    <td>{$primerIngreso}</td>
                    <td>{$ultimoIngreso}</td>
                    <td class='num'>{$totalOperaciones}</td>
                </tr>
            ";
        }

        if ($rows === '') {
            $rows = "
                <tr>
                    <td colspan='7' class='empty'>No hay datos para exportar</td>
                </tr>
            ";
        }

        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body {
                    font-family: 'DejaVu Sans', sans-serif;
                    font-size: 11px;
                    color: #222;
                    margin: 24px 28px;
                }

                h1 {
                    margin: 0 0 6px 0;
                    font-size: 18px;
                }

                .meta {
                    margin-bottom: 16px;
                    color: #555;
                    font-size: 10px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                }

                thead th {
                    background: #f2f2f2;
                    border: 1px solid #ccc;
                    padding: 7px;
                    text-align: left;
                    font-size: 10px;
                }

                tbody td {
                    border: 1px solid #ddd;
                    padding: 7px;
                }

                .num {
                    text-align: right;
                }

                .empty {
                    text-align: center;
                    color: #666;
                    padding: 12px;
                }
            </style>
        </head>
        <body>
            <h1>Ingresos de empleados</h1>
            <div class='meta'>Generado el {$fecha}</div>

            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Ingresos</th>
                        <th>Primer ingreso</th>
                        <th>Último ingreso</th>
                        <th>Operaciones</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
        </body>
        </html>";
    }
}