<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Notifications\RestablecerContrasena;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** «¿Olvidó su contraseña?»: enlace por correo y contraseña nueva. */
class RecuperarContrasenaTest extends TestCase
{
    use RefreshDatabase;

    public function test_envia_el_enlace_y_permite_crear_una_contrasena_nueva(): void
    {
        Notification::fake();
        $usuario = Usuario::factory()->emprendedor()->create(['correo' => 'maria@correo.com']);

        $this->get('/login')->assertSee('¿Olvidó su contraseña?');
        $this->from('/contrasena/olvido')->post('/contrasena/olvido', ['correo' => 'MARIA@correo.com'])
            ->assertRedirect('/contrasena/olvido')
            ->assertSessionHas('enviado');

        $token = null;
        Notification::assertSentTo($usuario, RestablecerContrasena::class, function ($aviso) use ($usuario, &$token) {
            $correo = $aviso->toMail($usuario);
            $token = basename(parse_url($correo->actionUrl, PHP_URL_PATH));

            return $correo->subject === 'Cree su contraseña nueva de NEXO' && $correo->actionText === 'Crear contraseña nueva';
        });

        $this->get("/contrasena/nueva/{$token}?correo=maria@correo.com")->assertOk()->assertSee('Cree su contraseña nueva');

        $this->post('/contrasena/nueva', [
            'token' => $token, 'correo' => 'maria@correo.com',
            'contrasena' => 'NuevaClave2026', 'contrasena_confirmation' => 'NuevaClave2026',
        ])->assertRedirect('/login')->assertSessionHas('exito');

        $this->assertTrue(Auth::attempt(['correo' => 'maria@correo.com', 'password' => 'NuevaClave2026']));
    }

    public function test_no_revela_si_el_correo_existe(): void
    {
        Notification::fake();

        $this->from('/contrasena/olvido')->post('/contrasena/olvido', ['correo' => 'nadie@correo.com'])
            ->assertRedirect('/contrasena/olvido')
            ->assertSessionHas('enviado')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_un_enlace_invalido_no_cambia_la_contrasena(): void
    {
        Usuario::factory()->emprendedor()->create(['correo' => 'maria@correo.com', 'contrasena' => 'Original2026']);

        $this->from('/contrasena/nueva/falso')->post('/contrasena/nueva', [
            'token' => 'falso', 'correo' => 'maria@correo.com',
            'contrasena' => 'NuevaClave2026', 'contrasena_confirmation' => 'NuevaClave2026',
        ])->assertSessionHasErrors('correo');

        $this->assertTrue(Auth::attempt(['correo' => 'maria@correo.com', 'password' => 'Original2026']));
    }
}
