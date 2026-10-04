<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** HU-04: seguimiento del estado del pedido (RF-09, RF-10, RF-11, RN-04, RN-06). */
class SeguimientoTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $emprendedor;
    private Usuario $cuentaCliente;
    private Pedido $pedido;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emprendedor = Usuario::factory()->emprendedor()->create(['negocio' => 'Dulces María']);
        $this->cuentaCliente = Usuario::factory()->cliente()->create(['correo' => 'pedro@correo.com']);

        $propio = ['id_emprendedor' => $this->emprendedor->id_usuario];
        $cliente = Cliente::factory()->create($propio + ['nombre' => 'Pedro Ruiz', 'correo' => 'pedro@correo.com']);
        $cliente->vincularCon($this->cuentaCliente);
        $producto = Producto::factory()->create($propio + ['nombre' => 'Pastel', 'precio' => 100]);

        $this->actingAs($this->emprendedor)->post('/pedidos', [
            'id_cliente' => $cliente->id_cliente,
            'fecha' => now()->format('Y-m-d\TH:i'),
            'productos' => [['id_producto' => $producto->id_producto, 'cantidad' => 1]],
        ]);
        $this->pedido = Pedido::firstOrFail();
    }

    private function cambiar(int $estado, ?string $observacion = null)
    {
        return $this->post("/pedidos/{$this->pedido->id_pedido}/estado", ['id_estado' => $estado, 'observacion' => $observacion]);
    }

    public function test_actualiza_el_estado_y_lo_registra_en_el_historial(): void
    {
        $this->cambiar(EstadoPedido::EN_PROCESO, 'Horneando')->assertSessionHas('exito');

        $this->assertSame(EstadoPedido::EN_PROCESO, $this->pedido->fresh()->id_estado);

        $ultimo = $this->pedido->historial()->get()->last();
        $this->assertSame(EstadoPedido::EN_PROCESO, $ultimo->id_estado);
        $this->assertSame('Horneando', $ultimo->observacion);
        $this->assertSame($this->emprendedor->id_usuario, $ultimo->id_usuario);
    }

    public function test_usa_una_observacion_predeterminada_si_no_se_escribe(): void
    {
        $this->cambiar(EstadoPedido::LISTO);

        $this->assertSame('Pedido listo para entrega.', $this->pedido->historial()->get()->last()->observacion);
    }

    public function test_recorre_el_flujo_completo_y_conserva_todo_el_historial(): void
    {
        foreach ([EstadoPedido::EN_PROCESO, EstadoPedido::LISTO, EstadoPedido::ENTREGADO] as $estado) {
            $this->cambiar($estado)->assertSessionHasNoErrors();
        }

        $this->assertSame(
            [EstadoPedido::NUEVO, EstadoPedido::EN_PROCESO, EstadoPedido::LISTO, EstadoPedido::ENTREGADO],
            $this->pedido->historial()->pluck('id_estado')->all(),
        );
    }

    public function test_no_permite_retroceder_ni_repetir_el_estado(): void
    {
        $this->cambiar(EstadoPedido::LISTO);

        $this->cambiar(EstadoPedido::EN_PROCESO)->assertSessionHasErrors('id_estado');
        $this->cambiar(EstadoPedido::LISTO)->assertSessionHasErrors('id_estado');
        $this->cambiar(999)->assertSessionHasErrors('id_estado');

        $this->assertSame(EstadoPedido::LISTO, $this->pedido->fresh()->id_estado);
        $this->assertSame(2, $this->pedido->historial()->count());
    }

    public function test_un_pedido_entregado_o_cancelado_ya_no_cambia(): void
    {
        $this->cambiar(EstadoPedido::ENTREGADO);

        $this->cambiar(EstadoPedido::CANCELADO, 'Tarde')->assertSessionHasErrors('id_estado');
        $this->assertSame(EstadoPedido::ENTREGADO, $this->pedido->fresh()->id_estado);
    }

    public function test_cancelar_exige_indicar_el_motivo(): void
    {
        $this->cambiar(EstadoPedido::CANCELADO)->assertSessionHasErrors('observacion');
        $this->assertSame(EstadoPedido::NUEVO, $this->pedido->fresh()->id_estado);

        $this->cambiar(EstadoPedido::CANCELADO, 'El cliente ya no lo necesita')->assertSessionHasNoErrors();
        $this->assertSame(EstadoPedido::CANCELADO, $this->pedido->fresh()->id_estado);

        $this->get("/pedidos/{$this->pedido->id_pedido}")->assertSee('Pedido cancelado')->assertDontSee('Actualizar estado');
    }

    public function test_otros_usuarios_no_pueden_cambiar_el_estado(): void
    {
        $this->actingAs(Usuario::factory()->emprendedor()->create());
        $this->cambiar(EstadoPedido::EN_PROCESO)->assertNotFound();

        $this->actingAs($this->cuentaCliente);
        $this->cambiar(EstadoPedido::EN_PROCESO)->assertForbidden();

        $this->assertSame(EstadoPedido::NUEVO, $this->pedido->fresh()->id_estado);
    }

    public function test_el_tablero_muestra_solo_los_pedidos_activos(): void
    {
        // El tablero de seguimiento es parte de «Hoy»; la ruta antigua lleva ahí.
        $this->get('/seguimiento')->assertRedirect('/panel');

        $this->get('/panel')->assertOk()->assertSee('Pedro Ruiz')->assertSee('Iniciar preparación')->assertSee('1 pedido por atender');

        $this->cambiar(EstadoPedido::ENTREGADO);

        $this->get('/panel')->assertOk()->assertDontSee('Pedro Ruiz')->assertSee('No tiene pedidos pendientes');
    }

    public function test_hoy_muestra_primero_los_pedidos_mas_antiguos(): void
    {
        $antiguo = $this->pedido->replicate()->fill(['fecha' => now()->subDays(3)]);
        $antiguo->id_emprendedor = $this->emprendedor->id_usuario;
        $antiguo->save();

        $this->get('/panel'); // consume el aviso «Pedido #0001 registrado» que dejó setUp()
        $html = $this->get('/panel')->getContent();

        $this->assertLessThan(
            strpos($html, '#'.$this->pedido->numero()),
            strpos($html, '#'.$antiguo->numero()),
        );
    }

    public function test_hoy_resume_las_ventas_de_los_ultimos_7_dias(): void
    {
        $this->get('/panel')->assertSee('Sin ventas en los últimos 7 días');

        $this->cambiar(EstadoPedido::ENTREGADO);

        $this->get('/panel')->assertSee('En los últimos 7 días vendió')->assertSee('Q 100.00')->assertSee('1 entrega');
    }

    public function test_el_detalle_muestra_el_panel_para_actualizar_el_estado(): void
    {
        $this->get("/pedidos/{$this->pedido->id_pedido}")
            ->assertOk()
            ->assertSee('Actualizar estado')
            ->assertSee('Progreso del pedido', false);
    }

    public function test_el_cliente_consulta_el_seguimiento_de_su_pedido(): void
    {
        $this->cambiar(EstadoPedido::EN_PROCESO, 'Estamos decorando su pastel');

        $this->actingAs($this->cuentaCliente)
            ->get("/mis-pedidos/{$this->pedido->id_pedido}")
            ->assertOk()
            ->assertSee('Dulces María')
            ->assertSee('Estamos decorando su pastel')
            ->assertSee('En proceso')
            ->assertDontSee('Actualizar estado');
    }

    public function test_el_cliente_no_puede_ver_pedidos_ajenos(): void
    {
        $otroCliente = Usuario::factory()->cliente()->create();

        $this->actingAs($otroCliente)->get("/mis-pedidos/{$this->pedido->id_pedido}")->assertNotFound();
        $this->actingAs($this->emprendedor)->get("/mis-pedidos/{$this->pedido->id_pedido}")->assertForbidden();
    }

    public function test_el_detalle_cancela_con_motivo_y_muestra_el_cierre(): void
    {
        $url = "/pedidos/{$this->pedido->id_pedido}";
        $this->get($url)->assertSee('Motivo de la cancelación')->assertSee('Editar pedido');

        // El formulario del diálogo envía el motivo junto con el estado Cancelado.
        $this->post("{$url}/estado", ['id_estado' => EstadoPedido::CANCELADO, 'observacion' => 'Cambió de fecha', 'cancelacion' => '1'])
            ->assertSessionHasNoErrors();

        $this->get($url)->assertOk()
            ->assertSee('Cancelado el '.now()->format('d/m/Y'))
            ->assertSee('Motivo: Cambió de fecha')
            ->assertDontSee('Editar pedido')
            ->assertDontSee('Motivo de la cancelación');
    }
}
