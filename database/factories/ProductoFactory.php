<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_emprendedor' => Usuario::factory()->emprendedor(),
            'nombre' => ucfirst(fake()->words(2, true)),
            'descripcion' => fake()->sentence(),
            'precio' => fake()->randomFloat(2, 5, 300),
            'estado' => true,
        ];
    }
}
