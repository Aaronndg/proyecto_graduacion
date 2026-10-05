<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Laravel\Socialite\Two\User as UsuarioGoogle;
use Mockery;
use Tests\TestCase;

/** «Continuar con Google»: entrar a una cuenta existente o crear una nueva con lo mínimo. */
class EntrarConGoogleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google.client_id' => 'id-de-prueba', 'services.google.client_secret' => 'secreto']);
    }

    private function googleDevuelve(string $correo, string $nombre = 'Ana García', string $id = 'g-123'): void
    {
        $usuario = (new UsuarioGoogle)->map(['id' => $id, 'name' => $nombre, 'email' => $correo]);
        $proveedor = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $proveedor->shouldReceive('user')->andReturn($usuario);
        $proveedor->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));
        $fabrica = Mockery::mock(Socialite::class);
        $fabrica->shouldReceive('driver')->with('google')->andReturn($proveedor);
        $this->app->instance(Socialite::class, $fabrica);
    }

    public function test_el_boton_aparece_solo_con_las_claves_configuradas(): void
    {
        $this->get('/login')->assertSee('Continuar con Google');

        config(['services.google.client_id' => null]);
        $this->get('/login')->assertDontSee('Continuar con Google');
    }

    public function test_entra_a_la_cuenta_que_ya_tiene_ese_correo(): void
    {
        $usuario = Usuario::factory()->emprendedor()->create(['correo' => 'maria@gmail.com']);
        $this->googleDevuelve('MARIA@gmail.com', 'María', 'g-777');

        $this->get('/auth/google/callback')->assertRedirect(route('panel'));

        $this->assertAuthenticatedAs($usuario);
        $this->assertSame('g-777', $usuario->fresh()->google_id);
    }

    public function test_una_cuenta_nueva_solo_pide_como_la_va_a_usar(): void
    {
        $this->googleDevuelve('ana@gmail.com');

        $this->get('/auth/google/callback')->assertRedirect(route('google.completar'));
        $this->get('/auth/google/completar')->assertOk()->assertSee('Ya casi está')->assertSee('ana@gmail.com');

        $this->from('/auth/google/completar')->post('/auth/google/completar', ['tipo' => 'emprendedor', 'nombre' => 'Ana García'])
            ->assertSessionHasErrors(['negocio' => 'Escriba el nombre de su negocio.']);

        $this->post('/auth/google/completar', ['tipo' => 'emprendedor', 'nombre' => 'Ana García', 'negocio' => 'Tortillas Ana'])
            ->assertRedirect(route('panel'));

        $usuario = Usuario::where('correo', 'ana@gmail.com')->firstOrFail();
        $this->assertSame(Rol::EMPRENDEDOR, $usuario->id_rol);
        $this->assertSame('g-123', $usuario->google_id);
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_el_cliente_invitado_queda_vinculado_con_su_codigo(): void
    {
        $emprendedor = Usuario::factory()->emprendedor()->create();
        $cliente = Cliente::factory()->create(['id_emprendedor' => $emprendedor->id_usuario]);
        $this->googleDevuelve('ana@gmail.com');

        $this->get('/auth/google?codigo='.$cliente->codigoFormateado())->assertRedirect('https://accounts.google.com/o/oauth2/auth');
        $this->get('/auth/google/callback');
        $this->get('/auth/google/completar')->assertSee($cliente->codigoFormateado());

        $this->post('/auth/google/completar', ['tipo' => 'cliente', 'nombre' => 'Ana', 'codigo' => $cliente->codigoFormateado()])
            ->assertRedirect(route('panel'))
            ->assertSessionHas('exito', 'Su cuenta está lista. Ya puede ver sus pedidos.');

        $this->assertSame(Usuario::where('correo', 'ana@gmail.com')->value('id_usuario'), $cliente->fresh()->id_usuario);
    }

    public function test_una_cuenta_desactivada_no_entra(): void
    {
        Usuario::factory()->emprendedor()->create(['correo' => 'maria@gmail.com', 'activo' => false]);
        $this->googleDevuelve('maria@gmail.com');

        $this->get('/auth/google/callback')->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
