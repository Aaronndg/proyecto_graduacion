<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** RF-03, RN-09 y RN-10: acceso según rol y aislamiento de datos entre emprendedores. */
class ControlAccesoTest extends TestCase
{
    use RefreshDatabase;

    public function test_cada_rol_ve_su_propio_panel(): void
    {
        $this->actingAs(Usuario::factory()->administrador()->create())->get('/panel')->assertOk()->assertSee('Emprendedores');
        $this->actingAs(Usuario::factory()->emprendedor()->create())->get('/panel')->assertOk()->assertSee('Pedidos por atender');
        $this->actingAs(Usuario::factory()->cliente()->create())->get('/panel')->assertOk()->assertSee('Mis pedidos');
    }

    public function test_solo_el_administrador_accede_a_la_gestion_de_usuarios(): void
    {
        $this->actingAs(Usuario::factory()->emprendedor()->create())->get('/admin/usuarios')->assertForbidden();
        $this->actingAs(Usuario::factory()->cliente()->create())->get('/admin/usuarios')->assertForbidden();
        $this->actingAs(Usuario::factory()->administrador()->create())->get('/admin/usuarios')->assertOk();
    }

    public function test_el_menu_no_muestra_opciones_no_autorizadas(): void
    {
        $this->actingAs(Usuario::factory()->emprendedor()->create())
            ->get('/panel')
            ->assertDontSee(route('admin.usuarios.index'));
    }

    public function test_cada_emprendedor_solo_ve_sus_propios_datos(): void
    {
        $ana = Usuario::factory()->emprendedor()->create();
        $luis = Usuario::factory()->emprendedor()->create();
        Cliente::factory()->count(2)->create(['id_emprendedor' => $ana->id_usuario]);
        Cliente::factory()->count(3)->create(['id_emprendedor' => $luis->id_usuario]);
        Producto::factory()->create(['id_emprendedor' => $luis->id_usuario]);

        $this->actingAs($ana);
        $this->assertSame(2, Cliente::count());
        $this->assertSame(0, Producto::count());

        $this->actingAs($luis);
        $this->assertSame(3, Cliente::count());
        $this->assertSame(1, Producto::count());
    }

    public function test_el_cliente_solo_ve_sus_propios_pedidos(): void
    {
        $emprendedor = Usuario::factory()->emprendedor()->create(['negocio' => 'Dulces María']);
        $cuenta = Usuario::factory()->cliente()->create(['correo' => 'pedro@correo.com']);
        $suyo = Cliente::factory()->create(['id_emprendedor' => $emprendedor->id_usuario, 'correo' => 'pedro@correo.com']);
        $ajeno = Cliente::factory()->create(['id_emprendedor' => $emprendedor->id_usuario]);
        $suyo->vincularCon($cuenta);

        $crear = fn (Cliente $cliente, float $total) => Pedido::withoutGlobalScopes()->forceCreate([
            'id_emprendedor' => $emprendedor->id_usuario,
            'id_cliente' => $cliente->id_cliente,
            'id_estado' => EstadoPedido::EN_PROCESO,
            'fecha' => now(),
            'total' => $total,
        ]);
        $crear($suyo, 150.50);
        $crear($ajeno, 999.99);

        $this->actingAs($cuenta)->get('/panel')
            ->assertOk()
            ->assertSee('Dulces María')
            ->assertSee('Q 150.50')
            ->assertSee('En proceso')
            ->assertDontSee('Q 999.99');

        $this->actingAs($emprendedor)->get('/panel')
            ->assertSee('Q 150.50')
            ->assertSee('Q 999.99');
    }

    public function test_los_registros_nuevos_se_asignan_al_emprendedor_autenticado(): void
    {
        $ana = Usuario::factory()->emprendedor()->create();
        $this->actingAs($ana);

        $cliente = Cliente::create(['nombre' => 'María López']);

        $this->assertSame($ana->id_usuario, $cliente->id_emprendedor);
    }
}
