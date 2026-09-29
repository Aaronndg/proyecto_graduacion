<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** HU-01: autenticación y acceso al sistema. */
class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_raiz_redirige_al_login_si_no_hay_sesion(): void
    {
        $this->get('/')->assertRedirect('/panel');
        $this->get('/panel')->assertRedirect('/login');
    }

    public function test_inicia_sesion_con_credenciales_validas(): void
    {
        $usuario = Usuario::factory()->create(['correo' => 'ana@correo.com']);

        $this->post('/login', ['correo' => 'ana@correo.com', 'contrasena' => 'Password123'])
            ->assertRedirect('/panel');

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_rechaza_credenciales_incorrectas(): void
    {
        Usuario::factory()->create(['correo' => 'ana@correo.com']);

        $this->from('/login')
            ->post('/login', ['correo' => 'ana@correo.com', 'contrasena' => 'incorrecta'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('correo');

        $this->assertGuest();
    }

    public function test_bloquea_despues_de_cinco_intentos_fallidos(): void
    {
        Usuario::factory()->create(['correo' => 'ana@correo.com']);

        foreach (range(1, 5) as $intento) {
            $this->post('/login', ['correo' => 'ana@correo.com', 'contrasena' => 'mala']);
        }

        $this->post('/login', ['correo' => 'ana@correo.com', 'contrasena' => 'Password123'])
            ->assertSessionHasErrors('correo');

        $this->assertGuest();
    }

    public function test_una_cuenta_desactivada_no_puede_iniciar_sesion(): void
    {
        Usuario::factory()->inactivo()->create(['correo' => 'ana@correo.com']);

        $this->post('/login', ['correo' => 'ana@correo.com', 'contrasena' => 'Password123'])
            ->assertSessionHasErrors(['correo' => __('auth.inactive')]);

        $this->assertGuest();
    }

    public function test_una_cuenta_desactivada_durante_la_sesion_es_expulsada(): void
    {
        $usuario = Usuario::factory()->create();
        $this->actingAs($usuario)->get('/panel')->assertOk();

        $usuario->update(['activo' => false]);

        $this->get('/panel')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_cierra_sesion(): void
    {
        $this->actingAs(Usuario::factory()->create())
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_registra_un_emprendedor_con_contrasena_protegida(): void
    {
        $this->post('/registro', [
            'tipo' => 'emprendedor',
            'nombre' => 'Lucía Ramírez',
            'negocio' => 'Pasteles Lucía',
            'correo' => 'LUCIA@Correo.com',
            'contrasena' => 'Secreta123',
            'contrasena_confirmation' => 'Secreta123',
        ])->assertRedirect('/panel');

        $usuario = Usuario::where('correo', 'lucia@correo.com')->firstOrFail();
        $this->assertSame(Rol::EMPRENDEDOR, $usuario->id_rol);
        $this->assertSame('Pasteles Lucía', $usuario->negocio);
        $this->assertNotSame('Secreta123', $usuario->contrasena); // RNF-02
        $this->assertTrue(Hash::check('Secreta123', $usuario->contrasena));
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_el_emprendedor_debe_indicar_su_negocio(): void
    {
        $this->post('/registro', [
            'tipo' => 'emprendedor',
            'nombre' => 'Lucía',
            'correo' => 'lucia@correo.com',
            'contrasena' => 'Secreta123',
            'contrasena_confirmation' => 'Secreta123',
        ])->assertSessionHasErrors('negocio');
    }

    public function test_valida_los_campos_obligatorios_y_la_contrasena(): void
    {
        $this->post('/registro', ['tipo' => 'cliente', 'contrasena' => 'corta', 'contrasena_confirmation' => 'otra'])
            ->assertSessionHasErrors(['nombre', 'correo', 'contrasena']);

        $this->assertSame(0, Usuario::count());
    }

    public function test_no_permite_autoregistrarse_como_administrador(): void
    {
        $this->post('/registro', [
            'tipo' => 'administrador',
            'nombre' => 'Intruso',
            'correo' => 'intruso@correo.com',
            'contrasena' => 'Secreta123',
            'contrasena_confirmation' => 'Secreta123',
        ])->assertSessionHasErrors('tipo');

        $this->assertDatabaseMissing('usuarios', ['correo' => 'intruso@correo.com']);
    }

    public function test_no_permite_correos_duplicados(): void
    {
        Usuario::factory()->create(['correo' => 'ana@correo.com']);

        $this->post('/registro', [
            'tipo' => 'cliente',
            'nombre' => 'Otra Ana',
            'correo' => 'ana@correo.com',
            'contrasena' => 'Secreta123',
            'contrasena_confirmation' => 'Secreta123',
        ])->assertSessionHasErrors('correo');
    }

    public function test_al_registrarse_un_cliente_se_vincula_con_sus_registros_por_correo(): void
    {
        $cliente = Cliente::factory()->create(['correo' => 'pedro@correo.com']);
        $this->assertNull($cliente->id_usuario);

        $this->post('/registro', [
            'tipo' => 'cliente',
            'nombre' => 'Pedro',
            'correo' => 'pedro@correo.com',
            'contrasena' => 'Secreta123',
            'contrasena_confirmation' => 'Secreta123',
        ])->assertRedirect('/panel');

        $this->assertSame(Usuario::where('correo', 'pedro@correo.com')->value('id_usuario'), $cliente->fresh()->id_usuario);
    }
}
