<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Catálogo para compartir: página pública con los productos activos del negocio. */
class CatalogoPublicoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_emprendedor_obtiene_su_enlace_y_el_publico_ve_solo_sus_productos_activos(): void
    {
        $maria = Usuario::factory()->emprendedor()->create(['negocio' => 'Dulces María', 'telefono' => '5512-3489']);
        $otro = Usuario::factory()->emprendedor()->create(['negocio' => 'Otro negocio']);
        Producto::factory()->create(['id_emprendedor' => $maria->id_usuario, 'nombre' => 'Pastel de chocolate', 'estado' => true]);
        Producto::factory()->create(['id_emprendedor' => $maria->id_usuario, 'nombre' => 'Brownies pausados', 'estado' => false]);
        Producto::factory()->create(['id_emprendedor' => $otro->id_usuario, 'nombre' => 'Tamal ajeno', 'estado' => true]);

        $this->actingAs($maria)->get('/productos')
            ->assertSee('Compartir su catálogo')
            ->assertSee(url('/catalogo/dulces-maria'));
        $this->assertSame('dulces-maria', $maria->fresh()->catalogo);

        auth()->logout();
        $this->get('/catalogo/dulces-maria')
            ->assertOk()
            ->assertSee('Dulces María')
            ->assertSee('Pastel de chocolate')
            ->assertSee('Enviar pedido')
            ->assertDontSee('Brownies pausados')
            ->assertDontSee('Tamal ajeno');
    }

    public function test_nombres_repetidos_reciben_una_direccion_distinta(): void
    {
        $a = Usuario::factory()->emprendedor()->create(['negocio' => 'Dulces María']);
        $b = Usuario::factory()->emprendedor()->create(['negocio' => 'Dulces María']);

        $this->assertStringEndsWith('/catalogo/dulces-maria', $a->enlaceCatalogo());
        $this->assertStringEndsWith('/catalogo/dulces-maria-2', $b->enlaceCatalogo());
    }

    public function test_un_catalogo_que_no_existe_o_de_una_cuenta_desactivada_no_se_muestra(): void
    {
        $this->get('/catalogo/no-existe')->assertNotFound();

        $desactivada = Usuario::factory()->emprendedor()->create(['negocio' => 'Cerrado', 'activo' => false]);
        $desactivada->enlaceCatalogo();
        $this->get('/catalogo/cerrado')->assertNotFound();
    }

    public function test_sin_whatsapp_muestra_los_productos_sin_el_boton_de_pedido(): void
    {
        $negocio = Usuario::factory()->emprendedor()->create(['negocio' => 'Sin Teléfono']);
        Producto::factory()->create(['id_emprendedor' => $negocio->id_usuario, 'nombre' => 'Pan dulce', 'estado' => true]);
        $negocio->enlaceCatalogo();

        $this->get('/catalogo/sin-telefono')->assertOk()->assertSee('Pan dulce')->assertDontSee('Enviar pedido');
    }
}
