<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Cambios pedidos en la revisión de la Entrega 3 y medidas de seguridad. */
class RevisionEntrega3Test extends TestCase
{
    use RefreshDatabase;

    private function usuario(int $rol, string $correo): Usuario
    {
        return Usuario::factory()->create(['id_rol' => $rol, 'correo' => $correo, 'contrasena' => 'Clave12345']);
    }

    public function test_reportes_muestra_un_boton_por_formato(): void
    {
        $emp = $this->usuario(Rol::EMPRENDEDOR, 'emp@pedidos.test');

        $this->actingAs($emp)->get(route('reportes'))
            ->assertOk()
            ->assertSee(route('reportes.excel'), false)
            ->assertSee(route('reportes.pdf'), false)
            ->assertSee(route('reportes.exportar'), false)
            ->assertSee('data-imprimir', false);
    }

    public function test_el_cliente_ve_siempre_el_formulario_para_vincular_otro_negocio(): void
    {
        $cli = $this->usuario(Rol::CLIENTE, 'cli@pedidos.test');

        $this->actingAs($cli)->get(route('panel'))->assertOk()->assertSee(route('vincular'), false);
    }

    public function test_la_cuenta_demo_no_cambia_correo_ni_contrasena(): void
    {
        $demo = $this->usuario(Rol::CLIENTE, 'cliente.demo@example.com');

        $this->actingAs($demo)->put(route('perfil.update'), ['nombre' => 'Otro', 'correo' => 'nuevo@pedidos.test'])->assertRedirect();
        $this->assertSame('cliente.demo@example.com', $demo->fresh()->correo);

        $this->actingAs($demo)->put(route('perfil.contrasena'), [
            'contrasena_actual' => 'Clave12345', 'contrasena' => 'Nueva12345', 'contrasena_confirmation' => 'Nueva12345',
        ])->assertSessionHasErrorsIn('contrasena', 'contrasena_actual');
        $this->assertTrue(Hash::check('Clave12345', $demo->fresh()->contrasena));
    }

    public function test_las_respuestas_llevan_encabezados_de_seguridad(): void
    {
        $this->get(route('login'))
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
