<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** RF-04: gestión de usuarios por el administrador. */
class GestionUsuariosTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Usuario::factory()->administrador()->create();
    }

    public function test_lista_y_filtra_usuarios(): void
    {
        Usuario::factory()->emprendedor()->create(['nombre' => 'Rosa Emprendedora']);
        Usuario::factory()->cliente()->create(['nombre' => 'Mario Cliente']);

        $this->actingAs($this->admin)
            ->get('/admin/usuarios?rol='.Rol::CLIENTE)
            ->assertOk()
            ->assertSee('Mario Cliente')
            ->assertDontSee('Rosa Emprendedora');

        $this->get('/admin/usuarios?buscar=Rosa')
            ->assertSee('Rosa Emprendedora')
            ->assertDontSee('Mario Cliente');
    }

    public function test_registra_un_usuario(): void
    {
        $this->actingAs($this->admin)->post('/admin/usuarios', [
            'nombre' => 'Jorge Méndez',
            'correo' => 'jorge@correo.com',
            'id_rol' => Rol::EMPRENDEDOR,
            'negocio' => 'Artesanías Jorge',
            'activo' => '1',
            'contrasena' => 'Secreta123',
            'contrasena_confirmation' => 'Secreta123',
        ])->assertRedirect('/admin/usuarios');

        $this->assertDatabaseHas('usuarios', ['correo' => 'jorge@correo.com', 'id_rol' => Rol::EMPRENDEDOR, 'negocio' => 'Artesanías Jorge']);
    }

    public function test_actualiza_un_usuario_sin_cambiar_la_contrasena(): void
    {
        $usuario = Usuario::factory()->cliente()->create();
        $hashAnterior = $usuario->contrasena;

        $this->actingAs($this->admin)->put("/admin/usuarios/{$usuario->id_usuario}", [
            'nombre' => 'Nombre Nuevo',
            'correo' => $usuario->correo,
            'id_rol' => Rol::CLIENTE,
            'activo' => '1',
            'contrasena' => '',
            'contrasena_confirmation' => '',
        ])->assertRedirect('/admin/usuarios');

        $usuario->refresh();
        $this->assertSame('Nombre Nuevo', $usuario->nombre);
        $this->assertSame($hashAnterior, $usuario->contrasena);
    }

    public function test_desactiva_y_reactiva_una_cuenta(): void
    {
        $usuario = Usuario::factory()->emprendedor()->create();

        $this->actingAs($this->admin)->patch("/admin/usuarios/{$usuario->id_usuario}/estado");
        $this->assertFalse($usuario->fresh()->activo);

        $this->patch("/admin/usuarios/{$usuario->id_usuario}/estado");
        $this->assertTrue($usuario->fresh()->activo);
    }

    public function test_el_administrador_no_puede_desactivarse_ni_quitarse_el_rol(): void
    {
        $this->actingAs($this->admin)
            ->patch("/admin/usuarios/{$this->admin->id_usuario}/estado")
            ->assertSessionHas('error');

        $this->put("/admin/usuarios/{$this->admin->id_usuario}", [
            'nombre' => $this->admin->nombre,
            'correo' => $this->admin->correo,
            'id_rol' => Rol::CLIENTE,
            'activo' => '0',
        ]);

        $this->admin->refresh();
        $this->assertTrue($this->admin->activo);
        $this->assertSame(Rol::ADMINISTRADOR, $this->admin->id_rol);
    }

    public function test_el_usuario_actualiza_su_perfil_y_contrasena(): void
    {
        $usuario = Usuario::factory()->emprendedor()->create();

        $this->actingAs($usuario)->put('/perfil', [
            'nombre' => 'Nombre Editado',
            'correo' => 'nuevo@correo.com',
            'negocio' => 'Negocio Editado',
        ])->assertSessionHas('exito');

        $this->put('/perfil/contrasena', [
            'contrasena_actual' => 'Password123',
            'contrasena' => 'NuevaClave456',
            'contrasena_confirmation' => 'NuevaClave456',
        ])->assertSessionHas('exito');

        $usuario->refresh();
        $this->assertSame('Negocio Editado', $usuario->negocio);
        $this->assertTrue(Hash::check('NuevaClave456', $usuario->contrasena));
    }

    public function test_rechaza_el_cambio_de_contrasena_si_la_actual_es_incorrecta(): void
    {
        $this->actingAs(Usuario::factory()->create())->put('/perfil/contrasena', [
            'contrasena_actual' => 'Equivocada1',
            'contrasena' => 'NuevaClave456',
            'contrasena_confirmation' => 'NuevaClave456',
        ])->assertSessionHasErrorsIn('contrasena', 'contrasena_actual');
    }
}
