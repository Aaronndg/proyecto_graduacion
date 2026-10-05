<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Teléfono (WhatsApp) y logo del negocio, visibles para sus clientes. */
class DatosNegocioTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_emprendedor_guarda_su_whatsapp_y_su_logo(): void
    {
        Storage::fake('public');
        $emprendedor = Usuario::factory()->emprendedor()->create(['negocio' => 'Dulces María']);

        $this->actingAs($emprendedor)->put('/perfil', [
            'nombre' => $emprendedor->nombre, 'correo' => $emprendedor->correo, 'negocio' => 'Dulces María',
            'telefono' => '5512-3489', 'logo' => UploadedFile::fake()->image('logo.png', 300, 300),
        ])->assertSessionHasNoErrors();

        $emprendedor->refresh();
        $this->assertSame('5512-3489', $emprendedor->telefono);
        Storage::disk('public')->assertExists($emprendedor->logo);

        $this->actingAs($emprendedor)->put('/perfil', [
            'nombre' => $emprendedor->nombre, 'correo' => $emprendedor->correo, 'negocio' => 'Dulces María', 'quitar_logo' => '1',
        ]);
        $this->assertNull($emprendedor->fresh()->logo);
    }

    public function test_el_cliente_puede_escribirle_al_negocio_por_su_pedido(): void
    {
        $emprendedor = Usuario::factory()->emprendedor()->create(['negocio' => 'Dulces María', 'telefono' => '5512-3489']);
        $cuenta = Usuario::factory()->cliente()->create();
        $cliente = Cliente::factory()->create(['id_emprendedor' => $emprendedor->id_usuario, 'nombre' => 'Ana García']);
        $cliente->vincularCon($cuenta);
        $pedido = Pedido::withoutGlobalScopes()->forceCreate([
            'id_emprendedor' => $emprendedor->id_usuario, 'id_cliente' => $cliente->id_cliente,
            'id_estado' => EstadoPedido::NUEVO, 'fecha' => now(), 'total' => 80,
        ]);

        $this->actingAs($cuenta)->get("/mis-pedidos/{$pedido->id_pedido}")
            ->assertSee('Escribir por WhatsApp')
            ->assertSee('https://wa.me/50255123489?text='.rawurlencode('Hola, soy Ana. Le escribo por mi pedido #'.$pedido->numero().'.'), false);
    }

    public function test_el_cliente_no_puede_poner_datos_de_negocio(): void
    {
        $cuenta = Usuario::factory()->cliente()->create();

        $this->actingAs($cuenta)->put('/perfil', ['nombre' => 'Ana', 'correo' => $cuenta->correo, 'telefono' => '5512-3489'])
            ->assertSessionHasNoErrors();

        $this->assertNull($cuenta->fresh()->telefono);
    }
}
