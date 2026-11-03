<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** RN-06: la invitación al cliente para que vea sus pedidos se ofrece en el momento oportuno y se entiende. */
class InvitacionClienteTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $emprendedor;
    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emprendedor = Usuario::factory()->emprendedor()->create(['negocio' => 'Dulces María']);
        $this->cliente = Cliente::factory()->create(['id_emprendedor' => $this->emprendedor->id_usuario, 'nombre' => 'Ana García', 'telefono' => '5512-3489']);
        $this->actingAs($this->emprendedor);
    }

    private function pedido(): Pedido
    {
        return Pedido::withoutGlobalScopes()->forceCreate([
            'id_emprendedor' => $this->emprendedor->id_usuario, 'id_cliente' => $this->cliente->id_cliente,
            'id_estado' => EstadoPedido::EN_PROCESO, 'fecha' => now(), 'total' => 150,
        ]);
    }

    public function test_al_registrar_un_cliente_se_ofrece_invitarle(): void
    {
        $respuesta = $this->post('/clientes', ['nombre' => 'Luis Pérez', 'telefono' => '5598-7766']);
        $respuesta->assertSessionHas('ofrecer_invitacion', true);

        $this->get($respuesta->headers->get('Location'))
            ->assertSee('¿Quiere que Luis vea sus pedidos desde su celular?')
            ->assertSee('Ahora no');
    }

    public function test_despues_de_crear_el_pedido_se_ofrece_que_lo_siga_desde_su_celular(): void
    {
        $pedido = $this->pedido();

        $this->withSession(['ofrecer_invitacion' => true])->get("/pedidos/{$pedido->id_pedido}")
            ->assertSee('¿Quiere que Ana siga este pedido desde su celular?')
            ->assertSee('https://wa.me/50255123489', false);

        // Sin el aviso de la sesión solo queda el acceso discreto en la sección del cliente
        $this->flushSession();
        $this->get("/pedidos/{$pedido->id_pedido}")
            ->assertDontSee('¿Quiere que Ana siga este pedido')
            ->assertSee('Invitar a ver sus pedidos');
    }

    public function test_no_se_ofrece_si_el_cliente_ya_ve_sus_pedidos(): void
    {
        $this->cliente->forceFill(['id_usuario' => Usuario::factory()->cliente()->create()->id_usuario, 'codigo_vinculacion' => null])->save();
        $pedido = $this->pedido();

        $this->withSession(['ofrecer_invitacion' => true])->get("/pedidos/{$pedido->id_pedido}")
            ->assertDontSee('¿Quiere que Ana siga este pedido')
            ->assertSee('Ve sus pedidos en su celular');
    }

    public function test_la_ficha_explica_la_invitacion_en_tres_pasos(): void
    {
        $this->get("/clientes/{$this->cliente->id_cliente}")
            ->assertSee('Invitar a ver sus pedidos')
            ->assertSee('Enviar invitación por WhatsApp')
            ->assertSee('Código de invitación')
            ->assertSee($this->cliente->codigoFormateado());
    }

    public function test_la_lista_de_clientes_se_filtra_por_invitacion(): void
    {
        $conCuenta = Cliente::factory()->create([
            'id_emprendedor' => $this->emprendedor->id_usuario, 'nombre' => 'Carlos Ruiz',
            'id_usuario' => Usuario::factory()->cliente()->create()->id_usuario,
        ]);

        $this->get('/clientes')->assertSee('Ana García')->assertSee('Carlos Ruiz')->assertSee('Sin invitar');
        $this->get('/clientes?invitacion=si')->assertSee('Carlos Ruiz')->assertDontSee('Ana García');
        $this->get('/clientes?invitacion=no')->assertSee('Ana García')->assertDontSee($conCuenta->nombre);
    }

    public function test_el_aviso_de_pedido_listo_incluye_la_invitacion_si_aun_no_tiene_cuenta(): void
    {
        $aviso = $this->cliente->avisoPedidoListo($this->pedido(), 'Dulces María');

        $this->assertStringContainsString('cree su cuenta aquí', rawurldecode($aviso));
        $this->assertStringContainsString($this->cliente->codigoFormateado(), rawurldecode($aviso));
    }

    public function test_el_cliente_ve_el_campo_como_codigo_de_invitacion(): void
    {
        $this->actingAs(Usuario::factory()->cliente()->create())
            ->get('/panel')
            ->assertSee('Escriba su código de invitación')
            ->assertSee('Código de invitación');
    }
}
