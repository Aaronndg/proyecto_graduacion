<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Aviso por WhatsApp, con el mensaje ya escrito, cuando el pedido queda listo. */
class AvisoPedidoListoTest extends TestCase
{
    use RefreshDatabase;

    private Pedido $pedido;
    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        $emprendedor = Usuario::factory()->emprendedor()->create(['negocio' => 'Dulces María']);
        $this->actingAs($emprendedor);
        $this->cliente = Cliente::factory()->create(['id_emprendedor' => $emprendedor->id_usuario, 'nombre' => 'Ana García', 'telefono' => '5512-3489']);
        $this->pedido = Pedido::withoutGlobalScopes()->forceCreate([
            'id_emprendedor' => $emprendedor->id_usuario, 'id_cliente' => $this->cliente->id_cliente,
            'id_estado' => EstadoPedido::EN_PROCESO, 'fecha' => now(), 'total' => 150,
        ]);
    }

    public function test_al_marcarlo_listo_ofrece_avisar_por_whatsapp_con_un_mensaje_sencillo(): void
    {
        $this->post("/pedidos/{$this->pedido->id_pedido}/estado", ['id_estado' => EstadoPedido::LISTO])
            ->assertSessionHas('accion', function (array $accion) {
                $mensaje = rawurldecode(parse_url($accion['url'], PHP_URL_QUERY));

                return $accion['texto'] === 'Avisar a Ana por WhatsApp'
                    && str_starts_with($accion['url'], 'https://wa.me/50255123489?text=')
                    && str_contains($mensaje, 'Hola Ana, le saluda Dulces María. ¡Su pedido ya está listo!')
                    && str_contains($mensaje, 'Q 150.00');
            });

        $this->get("/pedidos/{$this->pedido->id_pedido}")->assertSee('Avisar a Ana que ya está listo');
    }

    public function test_sin_telefono_explica_como_avisarle(): void
    {
        $this->cliente->update(['telefono' => null]);

        $this->post("/pedidos/{$this->pedido->id_pedido}/estado", ['id_estado' => EstadoPedido::LISTO])
            ->assertSessionMissing('accion');

        $this->get("/pedidos/{$this->pedido->id_pedido}")->assertSee('agregue el teléfono de Ana');
    }
}
