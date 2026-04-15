<?php

namespace App\Services;

use App\Exceptions\ResourceNotCreatedException;
use App\Repositories\ProductoRepository;
use Dompdf\Dompdf;
use Dompdf\Options;

final class ProductoQueryService
{
    public function __construct(
        private ProductoRepository $productoRepository,
    ) {}

    public function obtenerProductos(): array
    {
        return $this->productoRepository->getAllProductos();
    }
    
    public function exportarCsv(): string
    {
        $productos = $this->productoRepository->getAllForCsv();

        $handle = fopen('php://temp', 'w+');

        if ($handle === false) {
            throw new ResourceNotCreatedException('No se pudo generar el archivo CSV');
        }

        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($handle, ['nombre', 'sector', 'precio', 'cantidad']);

        foreach ($productos as $producto) {
            fputcsv($handle, [
                $producto['nombre'],
                $producto['sector'],
                $producto['precio'],
                $producto['cantidad'],
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        if ($csv === false) {
            throw new ResourceNotCreatedException('No se pudo leer el archivo CSV generado');
        }

        return $csv;
    }

    public function exportarPdf(): string
    {
        $productos = $this->productoRepository->getAllForPdf();

        $html = $this->buildProductosPdfHtml($productos);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdf = $dompdf->output();

        if ($pdf === '') {
            throw new ResourceNotCreatedException('No se pudo generar el archivo PDF');
        }

        return $pdf;
    }

    private function buildProductosPdfHtml(array $productos): string
    {
        $fecha = date('d/m/Y H:i');

        $rows = '';

        foreach ($productos as $producto) {
            $nombre = htmlspecialchars((string) $producto['nombre'], ENT_QUOTES, 'UTF-8');
            $sector = htmlspecialchars((string) $producto['sector'], ENT_QUOTES, 'UTF-8');
            $precio = number_format((float) $producto['precio'], 2, ',', '.');
            $cantidad = (int) $producto['cantidad'];

            $rows .= "
                <tr>
                    <td>{$nombre}</td>
                    <td>{$sector}</td>
                    <td class='num'>$ {$precio}</td>
                    <td class='num'>{$cantidad}</td>
                </tr>
            ";
        }

        if ($rows === '') {
            $rows = "
                <tr>
                    <td colspan='4' class='empty'>No hay productos para exportar</td>
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
                    font-size: 12px;
                    color: #222;
                    margin: 28px 34px;
                }

                h1 {
                    margin: 0 0 6px 0;
                    font-size: 20px;
                }

                .meta {
                    margin-bottom: 18px;
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
                    padding: 8px;
                    text-align: left;
                    font-size: 11px;
                }

                tbody td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    vertical-align: middle;
                }

                .num {
                    text-align: right;
                    white-space: nowrap;
                }

                .empty {
                    text-align: center;
                    color: #666;
                    padding: 14px;
                }

                .footer {
                    margin-top: 16px;
                    font-size: 10px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <h1>Listado de productos</h1>
            <div class='meta'>Generado el {$fecha}</div>

            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Sector</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>

            <div class='footer'>La Comanda - Exportación PDF</div>
        </body>
        </html>";
    }
}