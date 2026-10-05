<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Fecha de entrega del pedido: se guarda, se valida, ordena «Hoy» y se lee en palabras. */
class FechaEntregaTest extends TestCase
{
    use RefreshDatabase;

    private Cliente $cliente;
    private Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();
        $emprendedor = Usuario::factory()->emprendedor()->create();
        $this->actingAs($emprendedor);
        $this->cliente = Cliente::factory()->create(['id_emprendedor' => $emprendedor->id_usuario, 'nombre' => 'Ana García']);
        $this->producto = Producto::factory()->create(['id_emprendedor' => $emprendedor->id_usuario, 'precio' => 50]);
    }

    private function pedido(array $extra = []): array
    {
        return [
            'modo_cliente' => 'existente',
            'id_cliente' => $this->cliente->id_cliente,
            'fecha' => now()->format('Y-m-d\TH:i'),
            'productos' => [['id_producto' => $this->producto->id_producto, 'cantidad' => 1]],
        ] + $extra;
    }

    public function test_guarda_la_fecha_de_entrega_y_la_muestra_en_palabras(): void
    {
        $this->post('/pedidos', $this->pedido(['fecha_entrega' => today()->addDay()->toDateString()]))->assertSessionHasNoErrors();

        $pedido = Pedido::firstOrFail();
        $this->assertSame(today()->addDay()->toDateString(), $pedido->fecha_entrega->toDateString());
        $this->get("/pedidos/{$pedido->id_pedido}")->assertSee('Para mañana');

        // Es opcional y se puede quitar al editar.
        $this->put("/pedidos/{$pedido->id_pedido}", $this->pedido(['fecha_entrega' => '']))->assertSessionHasNoErrors();
        $this->assertNull($pedido->fresh()->fecha_entrega);
    }

    public function test_la_entrega_no_puede_ser_antes_del_pedido(): void
    {
        $this->from('/pedidos/crear')
            ->post('/pedidos', $this->pedido(['fecha_entrega' => today()->subDay()->toDateString()]))
            ->assertSessionHasErrors(['fecha_entrega' => 'La entrega no puede ser antes del día en que se hizo el pedido.']);
    }

    public function test_hoy_pone_primero_lo_que_hay_que_entregar_antes(): void
    {
        $this->post('/pedidos', $this->pedido(['fecha' => now()->subDays(3)->format('Y-m-d\TH:i')]));
        $this->cliente->update(['nombre' => 'Beto Urgente']);
        $this->post('/pedidos', $this->pedido(['fecha_entrega' => today()->toDateString()]));

        [$antiguo, $urgente] = Pedido::orderBy('id_pedido')->get()->all();

        $this->get('/panel'); // consume el aviso de «registrado»
        $html = $this->get('/panel')->assertSee('Para hoy')->getContent();

        $this->assertLessThan(strpos($html, '#'.$antiguo->numero()), strpos($html, '#'.$urgente->numero()), 'El pedido para hoy va antes que el más antiguo sin fecha.');
    }

    public function test_un_pedido_vencido_se_marca_como_atrasado(): void
    {
        $pedido = Pedido::withoutGlobalScopes()->forceCreate([
            'id_emprendedor' => $this->cliente->id_emprendedor, 'id_cliente' => $this->cliente->id_cliente,
            'id_estado' => 2, 'fecha' => now()->subDays(5), 'fecha_entrega' => today()->subDays(2), 'total' => 50,
        ]);

        $this->assertSame('atrasado', $pedido->entrega()['tono']);
        $this->get('/panel')->assertSee('Atrasado: era para el');
    }
}
