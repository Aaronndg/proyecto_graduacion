<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** RN-06: vinculación de la cuenta del cliente mediante el código entregado por el emprendedor. */
class VinculacionTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $emprendedor;
    private Usuario $cuenta;
    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emprendedor = Usuario::factory()->emprendedor()->create(['negocio' => 'Dulces María']);
        $this->cuenta = Usuario::factory()->cliente()->create();
        $this->cliente = Cliente::factory()->create(['id_emprendedor' => $this->emprendedor->id_usuario, 'telefono' => '5512-3489']);
    }

    public function test_cada_cliente_recibe_un_codigo_unico_y_legible(): void
    {
        $otro = Cliente::factory()->create();

        $this->assertMatchesRegularExpression('/^[A-HJKMNP-Z2-9]{8}$/', $this->cliente->codigo_vinculacion);
        $this->assertNotSame($this->cliente->codigo_vinculacion, $otro->codigo_vinculacion);
        $this->assertMatchesRegularExpression('/^[A-Z2-9]{4}-[A-Z2-9]{4}$/', $this->cliente->codigoFormateado());
    }

    public function test_el_emprendedor_ve_el_codigo_y_el_enlace_de_whatsapp(): void
    {
        $this->actingAs($this->emprendedor)
            ->get("/clientes/{$this->cliente->id_cliente}")
            ->assertOk()
            ->assertSee($this->cliente->codigoFormateado())
            ->assertSee('https://wa.me/50255123489', false);
    }

    public function test_el_cliente_vincula_su_cuenta_con_el_codigo(): void
    {
        $this->actingAs($this->cuenta)
            ->post('/vincular', ['codigo' => ' '.strtolower($this->cliente->codigoFormateado())])
            ->assertRedirect('/panel')
            ->assertSessionHas('exito');

        $this->cliente->refresh();
        $this->assertSame($this->cuenta->id_usuario, $this->cliente->id_usuario);
        $this->assertNull($this->cliente->codigo_vinculacion); // uso único
    }

    public function test_un_codigo_usado_no_sirve_para_otra_cuenta(): void
    {
        $codigo = $this->cliente->codigo_vinculacion;
        $this->actingAs($this->cuenta)->post('/vincular', ['codigo' => $codigo]);

        $this->actingAs(Usuario::factory()->cliente()->create())
            ->post('/vincular', ['codigo' => $codigo])
            ->assertSessionHasErrors('codigo');

        $this->assertSame($this->cuenta->id_usuario, $this->cliente->fresh()->id_usuario);
    }

    public function test_bloquea_despues_de_varios_codigos_incorrectos(): void
    {
        $this->actingAs($this->cuenta);

        foreach (range(1, 5) as $intento) {
            $this->post('/vincular', ['codigo' => 'ZZZZ-ZZZZ'])->assertSessionHasErrors('codigo');
        }

        // Aun con el código correcto, queda bloqueado temporalmente.
        $this->post('/vincular', ['codigo' => $this->cliente->codigo_vinculacion])
            ->assertSessionHasErrors(['codigo' => 'Demasiados intentos con códigos incorrectos. Intente de nuevo en 10 minuto(s).']);

        $this->assertNull($this->cliente->fresh()->id_usuario);
    }

    public function test_el_emprendedor_puede_desvincular_y_generar_un_codigo_nuevo(): void
    {
        $this->cliente->vincularCon($this->cuenta);

        $this->actingAs($this->emprendedor)->post("/clientes/{$this->cliente->id_cliente}/codigo")->assertSessionHas('exito');

        $this->cliente->refresh();
        $this->assertNull($this->cliente->id_usuario);
        $this->assertNotNull($this->cliente->codigo_vinculacion);

        $this->actingAs($this->cuenta)->get('/panel')->assertSee('Todavía no tiene pedidos asociados');
    }

    public function test_solo_los_roles_correctos_usan_la_vinculacion(): void
    {
        $this->actingAs($this->emprendedor)->post('/vincular', ['codigo' => 'X'])->assertForbidden();
        $this->actingAs($this->cuenta)->post("/clientes/{$this->cliente->id_cliente}/codigo")->assertForbidden();

        $otroEmprendedor = Usuario::factory()->emprendedor()->create();
        $this->actingAs($otroEmprendedor)->post("/clientes/{$this->cliente->id_cliente}/codigo")->assertNotFound();
    }
}
