<?php

namespace Database\Factories;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected static ?string $contrasena;

    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'correo' => fake()->unique()->safeEmail(),
            'contrasena' => static::$contrasena ??= Hash::make('Password123'),
            'id_rol' => Rol::EMPRENDEDOR,
            'negocio' => fake()->company(),
            'activo' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function administrador(): static
    {
        return $this->state(['id_rol' => Rol::ADMINISTRADOR, 'negocio' => null]);
    }

    public function emprendedor(): static
    {
        return $this->state(['id_rol' => Rol::EMPRENDEDOR]);
    }

    public function cliente(): static
    {
        return $this->state(['id_rol' => Rol::CLIENTE, 'negocio' => null]);
    }

    public function inactivo(): static
    {
        return $this->state(['activo' => false]);
    }
}
