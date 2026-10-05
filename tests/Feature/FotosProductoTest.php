<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** RF-12: foto opcional del producto (subir, reemplazar, quitar y validar). */
class FotosProductoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs(Usuario::factory()->emprendedor()->create());
    }

    private function datos(array $extra = []): array
    {
        return ['nombre' => 'Pastel de chocolate', 'precio' => '185.00', 'estado' => '1'] + $extra;
    }

    public function test_registra_un_producto_con_foto_y_la_muestra_en_el_catalogo(): void
    {
        $this->post('/productos', $this->datos(['imagen' => UploadedFile::fake()->image('pastel.jpg', 800, 600)]))
            ->assertRedirect('/productos')
            ->assertSessionHasNoErrors();

        $producto = Producto::firstOrFail();
        $this->assertNotNull($producto->imagen);
        Storage::disk('public')->assertExists($producto->imagen);

        $this->get('/productos')->assertSee($producto->urlImagen(), false);
    }

    public function test_reemplazar_o_quitar_la_foto_borra_el_archivo_anterior(): void
    {
        $this->post('/productos', $this->datos(['imagen' => UploadedFile::fake()->image('a.png')]));
        $producto = Producto::firstOrFail();
        $primera = $producto->imagen;

        $this->put("/productos/{$producto->id_producto}", $this->datos(['imagen' => UploadedFile::fake()->image('b.webp')]))
            ->assertSessionHasNoErrors();
        $segunda = $producto->fresh()->imagen;
        Storage::disk('public')->assertMissing($primera);
        Storage::disk('public')->assertExists($segunda);

        // Editar sin foto nueva la conserva.
        $this->put("/productos/{$producto->id_producto}", $this->datos(['precio' => '190.00']));
        $this->assertSame($segunda, $producto->fresh()->imagen);

        $this->put("/productos/{$producto->id_producto}", $this->datos(['quitar_imagen' => '1']));
        $this->assertNull($producto->fresh()->imagen);
        Storage::disk('public')->assertMissing($segunda);
    }

    public function test_rechaza_archivos_que_no_son_imagen_o_muy_pesados(): void
    {
        $this->from('/productos/crear')
            ->post('/productos', $this->datos(['imagen' => UploadedFile::fake()->create('lista.pdf', 100, 'application/pdf')]))
            ->assertSessionHasErrors(['imagen' => 'El archivo debe ser una imagen.']);

        $this->from('/productos/crear')
            ->post('/productos', $this->datos(['imagen' => UploadedFile::fake()->image('enorme.jpg')->size(3000)]))
            ->assertSessionHasErrors(['imagen' => 'La foto no debe pesar más de 2 MB.']);

        $this->assertSame(0, Producto::count());
    }

    public function test_eliminar_el_producto_borra_su_foto(): void
    {
        $this->post('/productos', $this->datos(['imagen' => UploadedFile::fake()->image('a.jpg')]));
        $producto = Producto::firstOrFail();

        $this->delete("/productos/{$producto->id_producto}")->assertRedirect('/productos');

        Storage::disk('public')->assertMissing($producto->imagen);
    }
}
