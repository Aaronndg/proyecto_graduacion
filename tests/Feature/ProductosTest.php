<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** HU-02 (productos) / RF-12. */
class ProductosTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $emprendedor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emprendedor = Usuario::factory()->emprendedor()->create();
        $this->actingAs($this->emprendedor);
    }

    public function test_registra_y_actualiza_un_producto(): void
    {
        $this->post('/productos', ['nombre' => 'Pastel de chocolate', 'descripcion' => 'Mediano', 'precio' => '85.00', 'estado' => '1'])
            ->assertRedirect('/productos');

        $producto = Producto::firstOrFail();
        $this->assertSame('85.00', $producto->precio);
        $this->assertTrue($producto->estado);

        $this->put("/productos/{$producto->id_producto}", ['nombre' => 'Pastel de chocolate', 'precio' => '90.50', 'estado' => '0'])
            ->assertRedirect('/productos');

        $producto->refresh();
        $this->assertSame('90.50', $producto->precio);
        $this->assertFalse($producto->estado);

        $this->get('/productos?estado=inactivos')->assertSee('Pastel de chocolate');
        $this->get('/productos?estado=activos')->assertDontSee('Pastel de chocolate');
    }

    public function test_valida_el_precio_y_el_nombre(): void
    {
        foreach (['0', '-5', 'abc', '10.555'] as $precio) {
            $this->post('/productos', ['nombre' => 'Galletas', 'precio' => $precio])->assertSessionHasErrors('precio');
        }
        $this->post('/productos', ['precio' => '10'])->assertSessionHasErrors('nombre');

        $this->assertSame(0, Producto::count());
    }

    public function test_no_permite_nombres_repetidos_en_el_mismo_catalogo(): void
    {
        Producto::factory()->create(['id_emprendedor' => $this->emprendedor->id_usuario, 'nombre' => 'Galletas']);

        $this->post('/productos', ['nombre' => 'Galletas', 'precio' => '10'])->assertSessionHasErrors('nombre');
    }

    public function test_no_puede_modificar_productos_de_otro_emprendedor(): void
    {
        $ajeno = Producto::factory()->create();

        $this->get("/productos/{$ajeno->id_producto}/editar")->assertNotFound();
        $this->put("/productos/{$ajeno->id_producto}", ['nombre' => 'X', 'precio' => '1'])->assertNotFound();
    }

    public function test_un_producto_usado_en_pedidos_no_se_elimina(): void
    {
        $usado = Producto::factory()->create(['id_emprendedor' => $this->emprendedor->id_usuario]);
        $libre = Producto::factory()->create(['id_emprendedor' => $this->emprendedor->id_usuario]);
        $cliente = Cliente::factory()->create(['id_emprendedor' => $this->emprendedor->id_usuario]);

        $this->post('/pedidos', [
            'id_cliente' => $cliente->id_cliente,
            'fecha' => now()->format('Y-m-d\TH:i'),
            'productos' => [['id_producto' => $usado->id_producto, 'cantidad' => 2]],
        ]);

        $this->delete("/productos/{$usado->id_producto}")->assertSessionHas('error');
        $this->assertModelExists($usado);

        $this->delete("/productos/{$libre->id_producto}")->assertRedirect('/productos');
        $this->assertModelMissing($libre);
    }

    public function test_se_pausa_y_activa_desde_el_catalogo(): void
    {
        $producto = Producto::factory()->create(['id_emprendedor' => $this->emprendedor->id_usuario, 'nombre' => 'Brownies', 'estado' => true]);

        $this->get('/productos')->assertSee('Pausar Brownies');
        $this->from('/productos')->patch("/productos/{$producto->id_producto}/disponible")
            ->assertRedirect('/productos')
            ->assertSessionHas('exito', '«Brownies» ya no aparece al crear pedidos.');
        $this->assertFalse($producto->fresh()->estado);

        $this->from('/productos')->patch("/productos/{$producto->id_producto}/disponible");
        $this->assertTrue($producto->fresh()->estado);

        // Solo sobre sus propios productos (RN-10)
        $ajeno = Producto::factory()->create();
        $this->patch("/productos/{$ajeno->id_producto}/disponible")->assertNotFound();
    }
}
