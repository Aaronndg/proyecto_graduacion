<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** HU-02 (clientes) / RF-05. */
class ClientesTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $emprendedor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emprendedor = Usuario::factory()->emprendedor()->create();
        $this->actingAs($this->emprendedor);
    }

    public function test_registra_consulta_y_actualiza_un_cliente(): void
    {
        $this->post('/clientes', [
            'nombre' => 'María López',
            'telefono' => '5512-3489',
            'correo' => 'MARIA@Email.com',
            'direccion' => 'Barrio El Centro, Jutiapa',
        ])->assertRedirect();

        $cliente = Cliente::firstOrFail();
        $this->assertSame($this->emprendedor->id_usuario, $cliente->id_emprendedor);
        $this->assertSame('maria@email.com', $cliente->correo);

        $this->get('/clientes')->assertOk()->assertSee('María López');
        $this->get('/clientes?buscar=5512')->assertSee('María López');
        $this->get('/clientes?buscar=zzz')->assertDontSee('María López');
        $this->get("/clientes/{$cliente->id_cliente}")->assertOk()->assertSee('Barrio El Centro');

        $this->put("/clientes/{$cliente->id_cliente}", ['nombre' => 'María L. Pérez', 'telefono' => '', 'correo' => ''])
            ->assertRedirect("/clientes/{$cliente->id_cliente}");

        $cliente->refresh();
        $this->assertSame('María L. Pérez', $cliente->nombre);
        $this->assertNull($cliente->telefono);
    }

    public function test_la_lista_muestra_cuando_fue_el_ultimo_pedido(): void
    {
        $con = Cliente::factory()->create(['id_emprendedor' => $this->emprendedor->id_usuario, 'nombre' => 'Ana Con Pedido']);
        Cliente::factory()->create(['id_emprendedor' => $this->emprendedor->id_usuario, 'nombre' => 'Beto Sin Pedido']);
        foreach ([10, 3] as $dias) {
            \App\Models\Pedido::withoutGlobalScopes()->forceCreate([
                'id_emprendedor' => $this->emprendedor->id_usuario,
                'id_cliente' => $con->id_cliente,
                'id_estado' => \App\Models\EstadoPedido::NUEVO,
                'fecha' => now()->subDays($dias),
                'total' => 50,
            ]);
        }

        $this->get('/clientes')->assertOk()
            ->assertSee('Último pedido')
            ->assertSee('último hace 3 días')
            ->assertDontSee('hace 1 semana')
            ->assertSee('Sin pedidos');
    }

    public function test_valida_los_datos_del_cliente(): void
    {
        $this->post('/clientes', ['nombre' => '', 'telefono' => 'abc', 'correo' => 'no-es-correo'])
            ->assertSessionHasErrors(['nombre', 'telefono', 'correo']);

        $this->assertSame(0, Cliente::count());
    }

    public function test_no_permite_correo_repetido_en_el_mismo_emprendimiento_pero_si_en_otro(): void
    {
        Cliente::factory()->create(['id_emprendedor' => $this->emprendedor->id_usuario, 'correo' => 'ana@correo.com']);
        $otro = Usuario::factory()->emprendedor()->create();

        $this->post('/clientes', ['nombre' => 'Ana', 'correo' => 'ana@correo.com'])->assertSessionHasErrors('correo');

        $this->actingAs($otro)->post('/clientes', ['nombre' => 'Ana', 'correo' => 'ana@correo.com'])->assertSessionHasNoErrors();
    }

    public function test_no_puede_ver_ni_modificar_clientes_de_otro_emprendedor(): void
    {
        $ajeno = Cliente::factory()->create();

        $this->get("/clientes/{$ajeno->id_cliente}")->assertNotFound();
        $this->get("/clientes/{$ajeno->id_cliente}/editar")->assertNotFound();
        $this->put("/clientes/{$ajeno->id_cliente}", ['nombre' => 'Hackeado'])->assertNotFound();
        $this->delete("/clientes/{$ajeno->id_cliente}")->assertNotFound();

        $this->assertNotSame('Hackeado', $ajeno->fresh()->nombre);
    }

    public function test_elimina_un_cliente_solo_si_no_tiene_pedidos(): void
    {
        $sinPedidos = Cliente::factory()->create(['id_emprendedor' => $this->emprendedor->id_usuario]);
        $conPedidos = Cliente::factory()->create(['id_emprendedor' => $this->emprendedor->id_usuario]);
        $producto = Producto::factory()->create(['id_emprendedor' => $this->emprendedor->id_usuario]);

        $this->post('/pedidos', [
            'id_cliente' => $conPedidos->id_cliente,
            'fecha' => now()->format('Y-m-d\TH:i'),
            'productos' => [['id_producto' => $producto->id_producto, 'cantidad' => 1]],
        ]);

        $this->delete("/clientes/{$conPedidos->id_cliente}")->assertSessionHas('error');
        $this->assertModelExists($conPedidos);

        $this->delete("/clientes/{$sinPedidos->id_cliente}")->assertRedirect('/clientes');
        $this->assertModelMissing($sinPedidos);
    }

    public function test_otros_roles_no_acceden_al_modulo_de_clientes(): void
    {
        $this->actingAs(Usuario::factory()->cliente()->create())->get('/clientes')->assertForbidden();
        $this->actingAs(Usuario::factory()->administrador()->create())->get('/clientes')->assertForbidden();
    }
}
