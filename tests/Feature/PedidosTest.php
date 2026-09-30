<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** HU-03: registro y administración de pedidos (RF-06, RF-07, RF-08, RN-01 a RN-03, RN-07). */
class PedidosTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $emprendedor;
    private Cliente $cliente;
    private Producto $pastel;
    private Producto $galletas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emprendedor = Usuario::factory()->emprendedor()->create();
        $propio = ['id_emprendedor' => $this->emprendedor->id_usuario];
        $this->cliente = Cliente::factory()->create($propio + ['nombre' => 'Ana García']);
        $this->pastel = Producto::factory()->create($propio + ['nombre' => 'Pastel', 'precio' => 85.00]);
        $this->galletas = Producto::factory()->create($propio + ['nombre' => 'Galletas', 'precio' => 12.50]);
        $this->actingAs($this->emprendedor);
    }

    private function datos(array $productos, array $extra = []): array
    {
        return $extra + [
            'modo_cliente' => 'existente',
            'id_cliente' => $this->cliente->id_cliente,
            'fecha' => '2026-09-29T10:30',
            'productos' => $productos,
        ];
    }

    public function test_registra_un_pedido_calculando_el_total_en_el_servidor(): void
    {
        $respuesta = $this->post('/pedidos', $this->datos([
            ['id_producto' => $this->pastel->id_producto, 'cantidad' => 2, 'precio_unitario' => '0.01'], // precio manipulado: se ignora
            ['id_producto' => $this->galletas->id_producto, 'cantidad' => 3],
        ]));

        $pedido = Pedido::with(['detalles', 'historial'])->firstOrFail();
        $respuesta->assertRedirect("/pedidos/{$pedido->id_pedido}");

        $this->assertSame('207.50', $pedido->total); // 2×85.00 + 3×12.50
        $this->assertSame($this->cliente->id_cliente, $pedido->id_cliente);
        $this->assertSame($this->emprendedor->id_usuario, $pedido->id_emprendedor);
        $this->assertSame(EstadoPedido::NUEVO, $pedido->id_estado);
        $this->assertSame('2026-09-29 10:30', $pedido->fecha->format('Y-m-d H:i'));
        $this->assertCount(2, $pedido->detalles);
        $this->assertSame('170.00', $pedido->detalles->firstWhere('id_producto', $this->pastel->id_producto)->subtotal);

        $this->assertCount(1, $pedido->historial);
        $this->assertSame(EstadoPedido::NUEVO, $pedido->historial[0]->id_estado);
        $this->assertSame($this->emprendedor->id_usuario, $pedido->historial[0]->id_usuario);
    }

    public function test_agrupa_productos_repetidos_en_una_sola_linea(): void
    {
        $this->post('/pedidos', $this->datos([
            ['id_producto' => $this->galletas->id_producto, 'cantidad' => 2],
            ['id_producto' => $this->galletas->id_producto, 'cantidad' => 3],
        ]));

        $detalles = Pedido::firstOrFail()->detalles;
        $this->assertCount(1, $detalles);
        $this->assertSame(5, $detalles[0]->cantidad);
    }

    public function test_registra_un_cliente_nuevo_junto_con_el_pedido(): void
    {
        $this->post('/pedidos', $this->datos(
            [['id_producto' => $this->pastel->id_producto, 'cantidad' => 1]],
            ['modo_cliente' => 'nuevo', 'id_cliente' => null, 'nuevo_cliente' => ['nombre' => 'Luis Gómez', 'telefono' => '5733-9014']],
        ))->assertSessionHasNoErrors();

        $nuevo = Cliente::where('nombre', 'Luis Gómez')->firstOrFail();
        $this->assertSame($this->emprendedor->id_usuario, $nuevo->id_emprendedor);
        $this->assertSame($nuevo->id_cliente, Pedido::firstOrFail()->id_cliente);
    }

    public function test_valida_cliente_y_productos_obligatorios(): void
    {
        $this->post('/pedidos', ['modo_cliente' => 'existente', 'fecha' => '2026-09-29T10:30', 'productos' => []])
            ->assertSessionHasErrors(['id_cliente', 'productos']);

        $this->post('/pedidos', $this->datos([['id_producto' => $this->pastel->id_producto, 'cantidad' => 0]]))
            ->assertSessionHasErrors('productos.0.cantidad');

        $this->post('/pedidos', $this->datos([], ['modo_cliente' => 'nuevo', 'id_cliente' => null, 'nuevo_cliente' => ['nombre' => '']]))
            ->assertSessionHasErrors(['nuevo_cliente.nombre', 'productos']);

        $this->assertSame(0, Pedido::count());
    }

    public function test_no_acepta_clientes_ni_productos_de_otro_emprendedor(): void
    {
        $clienteAjeno = Cliente::factory()->create();
        $productoAjeno = Producto::factory()->create();

        $this->post('/pedidos', $this->datos(
            [['id_producto' => $productoAjeno->id_producto, 'cantidad' => 1]],
            ['id_cliente' => $clienteAjeno->id_cliente],
        ))->assertSessionHasErrors(['id_cliente', 'productos.0.id_producto']);

        $this->assertSame(0, Pedido::count());
    }

    public function test_un_producto_inactivo_no_puede_agregarse_y_no_se_guarda_nada(): void
    {
        $this->galletas->update(['estado' => false]);

        $this->post('/pedidos', $this->datos(
            [['id_producto' => $this->galletas->id_producto, 'cantidad' => 1]],
            ['modo_cliente' => 'nuevo', 'id_cliente' => null, 'nuevo_cliente' => ['nombre' => 'No debe guardarse']],
        ))->assertSessionHasErrors('productos');

        $this->assertSame(0, Pedido::count());
        $this->assertDatabaseMissing('clientes', ['nombre' => 'No debe guardarse']); // la transacción se revierte
    }

    public function test_al_editar_se_conserva_el_precio_registrado(): void
    {
        $this->post('/pedidos', $this->datos([['id_producto' => $this->pastel->id_producto, 'cantidad' => 1]]));
        $pedido = Pedido::firstOrFail();

        $this->pastel->update(['precio' => 100.00]);
        $this->galletas->update(['precio' => 15.00]);

        $this->put("/pedidos/{$pedido->id_pedido}", $this->datos([
            ['id_producto' => $this->pastel->id_producto, 'cantidad' => 2],
            ['id_producto' => $this->galletas->id_producto, 'cantidad' => 1],
        ]))->assertRedirect("/pedidos/{$pedido->id_pedido}");

        $pedido->refresh()->load('detalles');
        $this->assertSame('85.00', $pedido->detalles->firstWhere('id_producto', $this->pastel->id_producto)->precio_unitario);
        $this->assertSame('15.00', $pedido->detalles->firstWhere('id_producto', $this->galletas->id_producto)->precio_unitario);
        $this->assertSame('185.00', $pedido->total); // 2×85 + 1×15
    }

    public function test_un_pedido_entregado_no_puede_editarse(): void
    {
        $this->post('/pedidos', $this->datos([['id_producto' => $this->pastel->id_producto, 'cantidad' => 1]]));
        $pedido = Pedido::firstOrFail();
        $pedido->update(['id_estado' => EstadoPedido::ENTREGADO]);

        $this->get("/pedidos/{$pedido->id_pedido}/editar")->assertRedirect("/pedidos/{$pedido->id_pedido}");
        $this->put("/pedidos/{$pedido->id_pedido}", $this->datos([['id_producto' => $this->pastel->id_producto, 'cantidad' => 9]]))
            ->assertSessionHasErrors('pedido');

        $this->assertSame('85.00', $pedido->fresh()->total);
    }

    public function test_consulta_y_filtra_pedidos(): void
    {
        $this->post('/pedidos', $this->datos([['id_producto' => $this->pastel->id_producto, 'cantidad' => 1]]));
        $pedido = Pedido::firstOrFail();

        $this->get('/pedidos')->assertOk()->assertSee('Ana García')->assertSee('Q 85.00');
        $this->get('/pedidos?buscar=%23'.$pedido->numero())->assertSee('Ana García');
        $this->get('/pedidos?buscar=Ana')->assertSee('Ana García');
        $this->get('/pedidos?estado='.EstadoPedido::ENTREGADO)->assertDontSee('Ana García');
        $this->get('/pedidos?desde=2026-09-30')->assertDontSee('Ana García');
        $this->get('/pedidos?desde=2026-09-29&hasta=2026-09-29')->assertSee('Ana García');

        $this->get("/pedidos/{$pedido->id_pedido}")->assertOk()
            ->assertSee('Pastel')
            ->assertSee('Pedido registrado en el sistema.')
            ->assertSee('Nuevo');
    }

    public function test_los_formularios_se_muestran_correctamente(): void
    {
        $this->get('/pedidos/create')->assertNotFound(); // la URL está en español
        $this->get('/pedidos/crear')->assertOk()->assertSee('Pastel')->assertSee('Ana García');
        $this->get("/pedidos/crear?cliente={$this->cliente->id_cliente}")->assertOk();

        $this->post('/pedidos', $this->datos([['id_producto' => $this->pastel->id_producto, 'cantidad' => 1]]));
        $this->get('/pedidos/'.Pedido::firstOrFail()->id_pedido.'/editar')->assertOk()->assertSee('Guardar cambios');
    }

    public function test_sin_productos_el_formulario_invita_a_registrarlos(): void
    {
        $nuevo = Usuario::factory()->emprendedor()->create();

        $this->actingAs($nuevo)->get('/pedidos/crear')->assertOk()->assertSee('No tiene productos activos');
    }

    public function test_no_puede_ver_pedidos_de_otro_emprendedor(): void
    {
        $this->post('/pedidos', $this->datos([['id_producto' => $this->pastel->id_producto, 'cantidad' => 1]]));
        $pedido = Pedido::firstOrFail();

        $this->actingAs(Usuario::factory()->emprendedor()->create())
            ->get("/pedidos/{$pedido->id_pedido}")
            ->assertNotFound();
    }
}
