<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CatalogosSeeder::class);

        if (app()->isLocal()) {
            $this->cuentasDeDemostracion();
            $this->call(DemoSeeder::class);

            return;
        }

        $this->administradorInicial();
    }

    /** Entorno local: tres cuentas conocidas para revisar el sistema. */
    private function cuentasDeDemostracion(): void
    {
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

    /**
     * Producción: solo el primer administrador, con el correo y la contraseña de ADMIN_CORREO y ADMIN_CONTRASENA (.env).
     * Nunca se crean cuentas con contraseñas conocidas. Los emprendedores se registran solos o los crea el administrador.
     */
    private function administradorInicial(): void
    {
        $datos = config('app.admin_inicial');

        $validacion = Validator::make($datos, [
            'nombre' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150'],
            'contrasena' => ['required', Password::defaults()],
        ]);

        if ($validacion->fails()) {
            throw new RuntimeException('Configure ADMIN_NOMBRE, ADMIN_CORREO y ADMIN_CONTRASENA en el archivo .env antes de preparar la base de datos: '
                .implode(' ', $validacion->errors()->all()));
        }

        Usuario::firstOrCreate(
            ['correo' => mb_strtolower($datos['correo'])],
            ['nombre' => $datos['nombre'], 'contrasena' => $datos['contrasena'], 'id_rol' => Rol::ADMINISTRADOR, 'activo' => true],
        );
    }
}
