<?php

namespace App\Services;

use App\DTO\Request\ProductoRequest;
use App\Exceptions\BadRequestException;
use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ResourceNotCreatedException;
use App\Repositories\ProductoRepository;
use App\Repositories\SectorRepository;
use Psr\Http\Message\UploadedFileInterface;

final class ProductoService
{
    public function __construct(
        private ProductoRepository $productoRepository,
        private SectorRepository $sectorRepo
    ) {}

    public function crearProducto(ProductoRequest $productoRequest): array
    {
        if ($this->productoRepository->productoNombreExiste($productoRequest->nombre)) {
            throw new ConflictException("El nombre de producto ya está en uso: {$productoRequest->nombre}");
        }

        $stockInicial = 100;

        $sector = $this->sectorRepo->getByClave($productoRequest->sector);

        if (!$sector) {
            throw new NotFoundException('Sector no encontrado');
        }

        $productoId = $this->productoRepository->agregarProducto(
            $productoRequest->nombre,
            $sector->id,
            $productoRequest->precio,
            $stockInicial
        );

        return [
            'id'       => $productoId,
            'nombre'   => $productoRequest->nombre,
            'sector'   => $productoRequest->sector->value,
            'precio'   => $productoRequest->precio,
            'cantidad' => $stockInicial
        ];
    }
    public function importarCsv(?UploadedFileInterface $file): array
    {
        if ($file === null) {
            throw new BadRequestException(
                'Debe enviarse un archivo CSV en el campo "file"'
                // Cambiá "file" por "archivo" si ese es el nombre real del campo en Postman
            );
        }

        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new BadRequestException('Error al subir el archivo CSV');
        }

        $stream = $file->getStream();
        $stream->rewind();
        $contents = $stream->getContents();

        if ($contents === '') {
            throw new BadRequestException('El archivo CSV está vacío');
        }

        // Quitar BOM UTF-8 si existe
        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents);

        $delimiter = $this->detectDelimiter($contents);

        $handle = fopen('php://temp', 'w+');

        if ($handle === false) {
            throw new ResourceNotCreatedException('No se pudo procesar el archivo CSV');
        }

        fwrite($handle, $contents);
        rewind($handle);

        $header = fgetcsv($handle, 0, $delimiter);

        if ($header === false) {
            fclose($handle);
            throw new BadRequestException('El archivo CSV está vacío');
        }

        $header = array_map(
            static fn($value) => trim(mb_strtolower((string) $value)),
            $header
        );

        $expectedHeader = ['nombre', 'sector', 'precio', 'cantidad'];

        if ($header !== $expectedHeader) {
            fclose($handle);
            throw new BadRequestException(
                'Encabezado CSV inválido. Se esperaba: nombre, sector, precio, cantidad. '
                . 'La columna "sector" debe contener la clave del sector '
                . '(por ejemplo: entrada, patio_trasero, cocina, candy_bar)'
            );
        }

        $creados = 0;
        $omitidos = 0;
        $filaNumero = 1;
        $errores = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $filaNumero++;

            if ($this->isEmptyCsvRow($row)) {
                continue;
            }

            if (count($row) !== 4) {
                $errores[] = "Fila {$filaNumero}: cantidad de columnas inválida. Se esperaban 4 columnas: nombre, sector, precio, cantidad";
                continue;
            }

            $nombre = trim((string) $row[0]);
            $sectorClave = trim(mb_strtolower((string) $row[1]));
            $precio = is_numeric($row[2]) ? (float) $row[2] : null;
            $cantidad = is_numeric($row[3]) ? (int) $row[3] : null;

            if ($nombre === '' || $sectorClave === '' || $precio === null || $cantidad === null) {
                $errores[] = "Fila {$filaNumero}: datos inválidos o incompletos";
                continue;
            }

            if ($precio < 0 || $cantidad < 0) {
                $errores[] = "Fila {$filaNumero}: precio o cantidad inválidos";
                continue;
            }

            $sectorId = $this->sectorRepo->findIdByClave($sectorClave);

            if ($sectorId === null) {
                $errores[] = "Fila {$filaNumero}: sector inexistente ({$sectorClave}). Debe enviarse la clave del sector";
                continue;
            }

            if ($this->productoRepository->existsByNombreAndSector($nombre, $sectorId)) {
                $omitidos++;
                continue;
            }

            $ok = $this->productoRepository->createFromCsv(
                $nombre,
                $sectorId,
                $precio,
                $cantidad
            );

            if (!$ok) {
                $errores[] = "Fila {$filaNumero}: no se pudo insertar el producto";
                continue;
            }

            $creados++;
        }

        fclose($handle);

        return [
            'creados' => $creados,
            'omitidos' => $omitidos,
            'errores' => $errores,
        ];
    }

    private function detectDelimiter(string $contents): string
    {
        $firstLine = strtok($contents, "\r\n") ?: '';

        $delimiters = [',', ';', "\t"];
        $bestDelimiter = ',';
        $bestCount = 0;

        foreach ($delimiters as $delimiter) {
            $columns = str_getcsv($firstLine, $delimiter);
            $count = count($columns);

            if ($count > $bestCount) {
                $bestCount = $count;
                $bestDelimiter = $delimiter;
            }
        }

        return $bestDelimiter;
    }

    private function isEmptyCsvRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }    

}