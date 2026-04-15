<?php

namespace App\Services;

use App\DTO\Request\EmpleadoRequest;
use App\Exceptions\BadRequestException;
use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Repositories\EmpleadoRepository;
use App\Repositories\TipoEmpleadoRepository;

final class EmpleadoService
{
    public function __construct(
        private EmpleadoRepository $empleadoRepo,
        private TipoEmpleadoRepository $tipoEmpleadoRepository,
    ) {}

    public function crearEmpleado(EmpleadoRequest $empleadoRequest): array
    {
        if ($this->empleadoRepo->emailExists($empleadoRequest->email)) {
            throw new ConflictException('El email ya está registrado');
        }

        $tipo = $this->tipoEmpleadoRepository->getTipoByString($empleadoRequest->tipo);

        if (!$tipo) {
            throw new BadRequestException('Tipo de empleado inválido');
        }

        try {
            $claveHasheada = password_hash($empleadoRequest->clave, PASSWORD_BCRYPT);
        } catch (\Throwable $e) {
            throw new \RuntimeException('No se pudo generar el hash de la contraseña', 0, $e);
        }

        $empleadoId = $this->empleadoRepo->registrarEmpleado(
            $empleadoRequest->nombre,
            $empleadoRequest->email,
            $claveHasheada,
            $tipo->id
        );

        return [
            'id'     => $empleadoId,
            'nombre' => $empleadoRequest->nombre,
            'email'  => $empleadoRequest->email,
            'tipo'   => $tipo->tipo,
            'estado' => 'activo',
        ];
    }

    public function registrarIngreso(int $empleadoId): void
    {
        $empleado = $this->empleadoRepo->getEmpleadoById($empleadoId);

        if (!$empleado) {
            throw new NotFoundException('Empleado no encontrado');
        }

        $hora = Utils::getHoraActual();

        $this->empleadoRepo->registrarIngreso($empleadoId, $hora);
    }

    public function obtenerEmpleados(): array
    {
        return $this->empleadoRepo->getEmpleados();
    }

    public function suspenderEmpleado(int $empleadoId): array
    {
        $this->getEmpleadoOrFail($empleadoId);

        $estadoActual = $this->empleadoRepo->getEmpleadoEstadoById($empleadoId);

        if ($estadoActual === null) {
            throw new NotFoundException('Empleado no encontrado');
        }

        if ($estadoActual === 'suspendido') {
            throw new ConflictException('El empleado ya está suspendido');
        }

        if ($estadoActual === 'borrado') {
            throw new ConflictException('No se puede suspender un empleado dado de baja');
        }

        $this->empleadoRepo->updateEstado($empleadoId, 'suspendido');

        return $this->empleadoRepo->getEmpleadoResumenById($empleadoId) ?? [
            'id' => $empleadoId,
            'estado' => 'suspendido'
        ];
    }

    public function reactivarEmpleado(int $empleadoId): array
    {
        $this->getEmpleadoOrFail($empleadoId);

        $estadoActual = $this->empleadoRepo->getEmpleadoEstadoById($empleadoId);

        if ($estadoActual === null) {
            throw new NotFoundException('Empleado no encontrado');
        }

        if ($estadoActual === 'activo') {
            throw new ConflictException('El empleado ya está activo');
        }

        $this->empleadoRepo->updateEstado($empleadoId, 'activo');

        return $this->empleadoRepo->getEmpleadoResumenById($empleadoId) ?? [
            'id' => $empleadoId,
            'estado' => 'activo'
        ];
    }

    public function borrarEmpleado(int $empleadoId): array
    {
        $this->getEmpleadoOrFail($empleadoId);

        $estadoActual = $this->empleadoRepo->getEmpleadoEstadoById($empleadoId);

        if ($estadoActual === null) {
            throw new NotFoundException('Empleado no encontrado');
        }

        if ($estadoActual === 'borrado') {
            throw new ConflictException('El empleado ya fue dado de baja');
        }

        $this->empleadoRepo->updateEstado($empleadoId, 'borrado');

        return $this->empleadoRepo->getEmpleadoResumenById($empleadoId) ?? [
            'id' => $empleadoId,
            'estado' => 'borrado'
        ];
    }

    private function getEmpleadoOrFail(int $empleadoId): void
    {
        $empleado = $this->empleadoRepo->getEmpleadoById($empleadoId);

        if (!$empleado) {
            throw new NotFoundException('Empleado no encontrado');
        }
    }
}