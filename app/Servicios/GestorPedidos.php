<?php

namespace App\Servicios;

use App\Models\Cliente;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Lógica de negocio del registro y actualización de pedidos (HU-03, RN-01 a RN-03, RN-07).
 * Los precios y totales se calculan en el servidor a partir del catálogo, nunca desde el navegador.
 */
class GestorPedidos
{
    public function registrar(array $datos, Usuario $usuario): Pedido
    {
        return DB::transaction(function () use ($datos, $usuario) {
            $cliente = $this->resolverCliente($datos);
            $lineas = $this->prepararLineas($datos['productos']);

            $pedido = Pedido::create([
                'id_cliente' => $cliente->id_cliente,
                'fecha' => $datos['fecha'],
                'id_estado' => EstadoPedido::NUEVO, // RN-03: estado inicial asignado por el sistema
                'total' => $this->total($lineas),
            ]);

            $pedido->detalles()->createMany($lineas->all());

            $pedido->historial()->create([
                'id_estado' => EstadoPedido::NUEVO,
                'id_usuario' => $usuario->id_usuario,
                'fecha_hora' => now(),
                'observacion' => EstadoPedido::find(EstadoPedido::NUEVO)->observacionPredeterminada(),
            ]);

            return $pedido;
        });
    }

    public function actualizar(Pedido $pedido, array $datos): Pedido
    {
        if (! $this->esEditable($pedido)) {
            throw ValidationException::withMessages(['pedido' => 'Un pedido entregado o cancelado ya no puede modificarse.']);
        }

        return DB::transaction(function () use ($pedido, $datos) {
            $cliente = $this->resolverCliente($datos);

            // Los productos que ya estaban en el pedido conservan el precio con el que se registraron.
            $preciosRegistrados = $pedido->detalles()->pluck('precio_unitario', 'id_producto');
            $lineas = $this->prepararLineas($datos['productos'], $preciosRegistrados);

            $pedido->update([
                'id_cliente' => $cliente->id_cliente,
                'fecha' => $datos['fecha'],
                'total' => $this->total($lineas),
            ]);

            $pedido->detalles()->delete();
            $pedido->detalles()->createMany($lineas->all());

            return $pedido;
        });
    }

    public function esEditable(Pedido $pedido): bool
    {
        return ! in_array($pedido->id_estado, EstadoPedido::FINALES, true);
    }

    /**
     * RN-04: el pedido solo avanza (puede saltar etapas) o se cancela; nunca retrocede
     * y los estados finales (Entregado, Cancelado) no cambian.
     *
     * @return Collection<int, EstadoPedido>
     */
    public function estadosSiguientes(Pedido $pedido): Collection
    {
        if (! $this->esEditable($pedido)) {
            return collect();
        }

        $ordenActual = EstadoPedido::whereKey($pedido->id_estado)->value('orden');

        return EstadoPedido::where(fn ($q) => $q
                ->where('orden', '>', $ordenActual)
                ->where('id_estado', '!=', EstadoPedido::CANCELADO))
            ->orWhere('id_estado', EstadoPedido::CANCELADO)
            ->orderBy('orden')
            ->get();
    }

    /** RF-09: actualiza el estado y deja constancia en el historial (quién, cuándo y por qué). */
    public function cambiarEstado(Pedido $pedido, int $idEstado, ?string $observacion, Usuario $usuario): Pedido
    {
        return DB::transaction(function () use ($pedido, $idEstado, $observacion, $usuario) {
            // Bloquea el registro para que dos cambios simultáneos no se pisen.
            $pedido = Pedido::whereKey($pedido->getKey())->lockForUpdate()->firstOrFail();
            $nuevo = $this->estadosSiguientes($pedido)->firstWhere('id_estado', $idEstado);

            if (! $nuevo) {
                throw ValidationException::withMessages([
                    'id_estado' => 'Ese cambio de estado no es válido para el estado actual del pedido.',
                ]);
            }

            if ($idEstado === EstadoPedido::CANCELADO && blank($observacion)) {
                throw ValidationException::withMessages(['observacion' => 'Indique el motivo de la cancelación.']);
            }

            $pedido->update(['id_estado' => $idEstado]);

            $pedido->historial()->create([
                'id_estado' => $idEstado,
                'id_usuario' => $usuario->id_usuario,
                'fecha_hora' => now(),
                'observacion' => filled($observacion) ? trim($observacion) : $nuevo->observacionPredeterminada(),
            ]);

            return $pedido;
        });
    }

    /** RN-01: el pedido siempre queda asociado a un cliente existente o registrado en el momento. */
    private function resolverCliente(array $datos): Cliente
    {
        if (($datos['modo_cliente'] ?? 'existente') === 'nuevo') {
            return Cliente::create($datos['nuevo_cliente']);
        }

        return Cliente::findOrFail($datos['id_cliente']);
    }

    /**
     * Agrupa productos repetidos (evita líneas duplicadas) y calcula precio y subtotal de cada línea.
     *
     * @param  array<int, array{id_producto: int|string, cantidad: int|string}>  $productos
     */
    private function prepararLineas(array $productos, ?Collection $preciosRegistrados = null): Collection
    {
        $cantidades = collect($productos)
            ->groupBy(fn ($linea) => (int) $linea['id_producto'])
            ->map(fn ($grupo) => $grupo->sum(fn ($linea) => (int) $linea['cantidad']));

        $catalogo = Producto::whereKey($cantidades->keys())->get()->keyBy('id_producto');

        return $cantidades->map(function (int $cantidad, int $idProducto) use ($catalogo, $preciosRegistrados) {
            $producto = $catalogo->get($idProducto);
            $precioRegistrado = $preciosRegistrados?->get($idProducto);

            if (! $producto || (! $producto->estado && $precioRegistrado === null)) {
                throw ValidationException::withMessages([
                    'productos' => 'Uno de los productos seleccionados no está disponible. Revise la lista de productos.',
                ]);
            }

            $precioCentavos = (int) round((float) ($precioRegistrado ?? $producto->precio) * 100);

            return [
                'id_producto' => $idProducto,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioCentavos / 100,
                'subtotal' => $precioCentavos * $cantidad / 100,
            ];
        })->values();
    }

    private function total(Collection $lineas): float
    {
        return $lineas->sum(fn ($linea) => (int) round($linea['subtotal'] * 100)) / 100;
    }
}
