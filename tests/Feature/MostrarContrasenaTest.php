<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Los campos de contraseña traen el botón para mostrar u ocultar lo escrito. */
class MostrarContrasenaTest extends TestCase
{
    use RefreshDatabase;

    public function test_los_formularios_con_contrasena_muestran_el_boton_del_ojo(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('data-ver-contrasena="contrasena"', false)
            ->assertSee('aria-label="Mostrar contraseña"', false);

        $this->get('/registro')
            ->assertSee('data-ver-contrasena="contrasena"', false)
            ->assertSee('data-ver-contrasena="contrasena_confirmation"', false);

        $this->actingAs(Usuario::factory()->emprendedor()->create())->get('/perfil')
            ->assertSee('data-ver-contrasena="contrasena_actual"', false)
            ->assertSee('data-ver-contrasena="contrasena_nueva"', false);
    }

    public function test_los_demas_campos_no_lo_traen(): void
    {
        $this->get('/login')->assertDontSee('data-ver-contrasena="correo"', false);
    }
}
