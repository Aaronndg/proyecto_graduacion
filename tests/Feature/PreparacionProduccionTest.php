<?php

namespace Tests\Feature;

use App\Console\Commands\CrearRespaldo;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

/** Antes de publicar: sin cuentas de demostración fuera del entorno local y con respaldos diarios de la base de datos. */
class PreparacionProduccionTest extends TestCase
{
    use RefreshDatabase;

    public function test_fuera_del_entorno_local_solo_se_crea_el_administrador_configurado(): void
    {
        config(['app.admin_inicial' => ['nombre' => 'Aarón Díaz', 'correo' => 'Admin@Nexo.gt', 'contrasena' => 'ClaveSegura2026']]);

        $this->seed(DatabaseSeeder::class);

        $admin = Usuario::where('correo', 'admin@nexo.gt')->first();
        $this->assertNotNull($admin);
        $this->assertSame(Rol::ADMINISTRADOR, $admin->id_rol);
        $this->assertTrue(Hash::check('ClaveSegura2026', $admin->contrasena));
        $this->assertSame(1, Usuario::count());
        $this->assertDatabaseMissing('usuarios', ['correo' => 'admin@pedidos.test']);
        $this->assertSame(0, Producto::count());
    }

    public function test_sin_datos_del_administrador_no_prepara_la_base_de_datos(): void
    {
        config(['app.admin_inicial' => ['nombre' => 'Administrador', 'correo' => null, 'contrasena' => 'corta']]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ADMIN_CORREO');

        $this->seed(DatabaseSeeder::class);
    }

    public function test_el_respaldo_guarda_la_estructura_y_los_datos_y_conserva_solo_los_ultimos(): void
    {
        Storage::fake('local');
        $maria = Usuario::factory()->emprendedor()->create(['negocio' => "Dulces D'María"]);
        Producto::factory()->create(['id_emprendedor' => $maria->id_usuario, 'nombre' => 'Pastel de chocolate']);
        foreach (['2026-01-01_030000', '2026-01-02_030000', '2026-01-03_030000'] as $fecha) {
            Storage::disk('local')->put(CrearRespaldo::CARPETA."/respaldo_{$fecha}.sql.gz", 'viejo');
        }

        $this->artisan('respaldo:crear', ['--conservar' => 2])->assertSuccessful();

        $archivos = collect(Storage::disk('local')->files(CrearRespaldo::CARPETA))->sort()->values();
        $this->assertCount(2, $archivos);
        $this->assertSame(CrearRespaldo::CARPETA.'/respaldo_2026-01-03_030000.sql.gz', $archivos[0]);

        $sql = gzdecode(Storage::disk('local')->get($archivos[1]));
        $this->assertStringContainsString('CREATE TABLE `pedidos`', $sql);
        $this->assertStringContainsString('INSERT INTO `productos`', $sql);
        $this->assertStringContainsString("'Pastel de chocolate'", $sql);
        $this->assertStringContainsString("'Dulces D\\'María'", $sql);
    }

    public function test_el_respaldo_esta_programado_a_diario(): void
    {
        $eventos = collect(app(Schedule::class)->events())
            ->filter(fn ($evento) => str_contains($evento->command ?? '', 'respaldo:crear'));

        $this->assertCount(1, $eventos);
        $this->assertSame('0 3 * * *', $eventos->first()->expression);
    }
}
