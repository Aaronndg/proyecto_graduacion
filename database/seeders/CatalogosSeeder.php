<?php

namespace Database\Seeders;

use App\Models\EstadoPedido;
use App\Models\Rol;
use Illuminate\Database\Seeder;

/** Datos base obligatorios: roles (4.5) y estados del pedido (RN-03, RN-04). */
class CatalogosSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [Rol::ADMINISTRADOR, 'Administrador', 'Administra los usuarios y supervisa la plataforma.'],
            [Rol::EMPRENDEDOR, 'Emprendedor', 'Gestiona clientes, productos, pedidos, estados y reportes de su emprendimiento.'],
            [Rol::CLIENTE, 'Cliente', 'Consulta la información y el seguimiento de sus propios pedidos.'],
        ];

        foreach ($roles as [$id, $nombre, $descripcion]) {
            Rol::updateOrCreate(['id_rol' => $id], ['nombre' => $nombre, 'descripcion' => $descripcion]);
        }

        $estados = [
            [EstadoPedido::NUEVO, 'Nuevo', 'Pedido registrado, pendiente de atención.', 1],
            [EstadoPedido::EN_PROCESO, 'En proceso', 'El pedido se está preparando.', 2],
            [EstadoPedido::LISTO, 'Listo', 'El pedido está listo para entrega.', 3],
            [EstadoPedido::ENTREGADO, 'Entregado', 'El pedido fue entregado al cliente.', 4],
            [EstadoPedido::CANCELADO, 'Cancelado', 'El pedido fue cancelado.', 5],
        ];

        foreach ($estados as [$id, $nombre, $descripcion, $orden]) {
            EstadoPedido::updateOrCreate(['id_estado' => $id], ['nombre' => $nombre, 'descripcion' => $descripcion, 'orden' => $orden]);
        }
    }
}
