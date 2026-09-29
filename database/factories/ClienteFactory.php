<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_emprendedor' => Usuario::factory()->emprendedor(),
            'nombre' => fake()->name(),
            'telefono' => fake()->numerify('####-####'),
            'correo' => fake()->unique()->safeEmail(),
            'direccion' => fake()->streetAddress().', Jutiapa',
        ];
    }
}
