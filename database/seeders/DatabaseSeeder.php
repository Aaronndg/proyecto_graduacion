<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CatalogosSeeder::class);

        // Cuentas de demostración para el entorno local. Cambie las contraseñas antes de producción.
        $cuentas = [
            ['Administrador', 'admin@pedidos.test', Rol::ADMINISTRADOR, null],
            ['María González', 'emprendedor@pedidos.test', Rol::EMPRENDEDOR, 'Dulces María'],
            ['Carlos Pérez', 'cliente@pedidos.test', Rol::CLIENTE, null],
        ];

        foreach ($cuentas as [$nombre, $correo, $rol, $negocio]) {
            Usuario::firstOrCreate(
                ['correo' => $correo],
                ['nombre' => $nombre, 'contrasena' => 'Password123', 'id_rol' => $rol, 'negocio' => $negocio, 'activo' => true],
            );
        }
    }
}
