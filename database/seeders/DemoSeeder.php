<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

/**
 * Datos de demostración para presentar el sistema: catálogo, clientes y pedidos en distintos estados
 * para la cuenta emprendedor@pedidos.test. Se ejecuta solo si esa cuenta aún no tiene pedidos.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $emprendedor = Usuario::where('correo', 'emprendedor@pedidos.test')->first();

        if (! $emprendedor || Pedido::withoutGlobalScopes()->where('id_emprendedor', $emprendedor->id_usuario)->exists()) {
            return;
        }

        mt_srand(2026);
        $id = $emprendedor->id_usuario;

        $productos = collect([
            ['Pastel de chocolate', 'Pastel mediano para 12 porciones', 185.00, 'chocolate'],
            ['Pastel de tres leches', 'Pastel mediano para 12 porciones', 165.00, 'tresleches'],
            ['Cupcake de vainilla', 'Con betún de mantequilla', 12.50, 'cupcake'],
            ['Galletas decoradas', 'Docena, decoración personalizada', 60.00, 'galletas'],
            ['Pie de limón', 'Pie entero de 8 porciones', 95.00, 'limon'],
            ['Brownies', 'Caja de 6 unidades', 45.00, 'brownie'],
            ['Rosca de canela', 'Rosca familiar', 55.00, 'rosca'],
            ['Quesadilla salvadoreña', 'Porción individual', 18.00, 'quesadilla'],
        ])->map(fn ($p) => Producto::withoutGlobalScopes()->forceCreate([
            'id_emprendedor' => $id, 'nombre' => $p[0], 'descripcion' => $p[1], 'precio' => $p[2], 'estado' => true,
            'imagen' => $this->imagenDemo($p[3]),
        ]));

        $clientes = collect([
            ['Carlos Pérez', '5841-2207', 'cliente@pedidos.test', 'Barrio El Centro, Jutiapa'],
            ['Ana Gómez', '5733-9014', 'ana.gomez@correo.com', 'Colonia Villa Real, Jutiapa'],
            ['Luis Morales', '4412-8870', null, 'Aldea El Progreso'],
            ['Sofía Hernández', '5520-1133', 'sofia.h@correo.com', 'Barrio La Esperanza, Jutiapa'],
            ['José Martínez', '3021-7788', null, 'Zona 1, Jutiapa'],
            ['Andrea Castillo', '5698-4410', 'andrea.c@correo.com', 'Residenciales Los Pinos'],
        ])->map(function ($c) use ($id) {
            $cliente = new Cliente(['nombre' => $c[0], 'telefono' => $c[1], 'correo' => $c[2], 'direccion' => $c[3]]);
            $cliente->id_emprendedor = $id;
            $cliente->save();

            // La cuenta demo del cliente queda vinculada, como si hubiera usado su código.
            if ($cuenta = Usuario::where('correo', $c[2] ?? '')->where('id_rol', \App\Models\Rol::CLIENTE)->first()) {
                $cliente->vincularCon($cuenta);
            }

            return $cliente;
        });

        // [días atrás, índice de cliente, estado final]
        $plan = [
            [28, 1, EstadoPedido::ENTREGADO], [25, 0, EstadoPedido::ENTREGADO], [22, 3, EstadoPedido::ENTREGADO],
            [20, 2, EstadoPedido::CANCELADO], [17, 5, EstadoPedido::ENTREGADO], [14, 0, EstadoPedido::ENTREGADO],
            [11, 4, EstadoPedido::ENTREGADO], [8, 1, EstadoPedido::ENTREGADO], [5, 3, EstadoPedido::LISTO],
            [3, 0, EstadoPedido::EN_PROCESO], [2, 5, EstadoPedido::EN_PROCESO], [1, 2, EstadoPedido::NUEVO],
            [0, 0, EstadoPedido::NUEVO],
        ];

        $observaciones = [
            EstadoPedido::NUEVO => 'Pedido registrado en el sistema.',
            EstadoPedido::EN_PROCESO => 'Pedido en preparación.',
            EstadoPedido::LISTO => 'Pedido listo para entrega.',
            EstadoPedido::ENTREGADO => 'Pedido entregado al cliente.',
            EstadoPedido::CANCELADO => 'El cliente canceló el pedido.',
        ];

        foreach ($plan as [$diasAtras, $indiceCliente, $estadoFinal]) {
            $fecha = now()->subDays($diasAtras)->setTime(mt_rand(8, 17), [0, 15, 30, 45][mt_rand(0, 3)]);

            $lineas = $productos->random(mt_rand(1, 3))->map(function (Producto $p) {
                $cantidad = mt_rand(1, 4);

                return ['id_producto' => $p->id_producto, 'cantidad' => $cantidad, 'precio_unitario' => $p->precio, 'subtotal' => $p->precio * $cantidad];
            });

            $pedido = Pedido::withoutGlobalScopes()->forceCreate([
                'id_emprendedor' => $id,
                'id_cliente' => $clientes[$indiceCliente]->id_cliente,
                'fecha' => $fecha,
                'total' => $lineas->sum('subtotal'),
                'id_estado' => $estadoFinal,
            ]);
            $pedido->detalles()->createMany($lineas->values()->all());

            // Recorrido de estados hasta el estado final.
            $recorrido = $estadoFinal === EstadoPedido::CANCELADO
                ? [EstadoPedido::NUEVO, EstadoPedido::CANCELADO]
                : range(EstadoPedido::NUEVO, $estadoFinal);

            foreach ($recorrido as $paso => $estado) {
                $pedido->historial()->create([
                    'id_estado' => $estado,
                    'id_usuario' => $id,
                    'fecha_hora' => $fecha->copy()->addHours($paso * 5)->min(now()),
                    'observacion' => $observaciones[$estado],
                ]);
            }
        }
    }

    /** Imagen ilustrada de ejemplo para el producto (en uso real, el negocio sube sus fotos). */
    private function imagenDemo(string $clave): ?string
    {
        $origen = database_path("seeders/imagenes/{$clave}.svg");
        if (! is_file($origen)) {
            return null;
        }

        $ruta = "productos/demo-{$clave}.svg";
        \Illuminate\Support\Facades\Storage::disk('public')->put($ruta, file_get_contents($origen));

        return $ruta;
    }
}
