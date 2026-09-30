<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** HU-05: historial, ventas y reportes (RF-11, RF-13, RF-14, RN-10). */
class ReportesTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $emprendedor;
    private Cliente $cliente;
    private Producto $pastel;
    private Producto $galletas;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-15 12:00:00');

        $this->emprendedor = Usuario::factory()->emprendedor()->create();
        $propio = ['id_emprendedor' => $this->emprendedor->id_usuario];
        $this->cliente = Cliente::factory()->create($propio + ['nombre' => 'Ana Gómez']);
        $this->pastel = Producto::factory()->create($propio + ['nombre' => 'Pastel', 'precio' => 100]);
        $this->galletas = Producto::factory()->create($propio + ['nombre' => 'Galletas', 'precio' => 20]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** @param  array<int, array{0: Producto, 1: int}>  $lineas */
    private function pedido(string $fecha, int $estado, array $lineas, ?Usuario $dueno = null, ?Cliente $cliente = null): Pedido
    {
        $detalles = collect($lineas)->map(fn ($l) => [
            'id_producto' => $l[0]->id_producto, 'cantidad' => $l[1], 'precio_unitario' => $l[0]->precio, 'subtotal' => $l[0]->precio * $l[1],
        ]);

        $pedido = Pedido::withoutGlobalScopes()->forceCreate([
            'id_emprendedor' => ($dueno ?? $this->emprendedor)->id_usuario,
            'id_cliente' => ($cliente ?? $this->cliente)->id_cliente,
            'fecha' => $fecha,
            'total' => $detalles->sum('subtotal'),
            'id_estado' => $estado,
        ]);
        $pedido->detalles()->createMany($detalles->all());

        return $pedido;
    }

    private function datosDeEjemplo(): void
    {
        $this->pedido('2026-09-01 10:00', EstadoPedido::ENTREGADO, [[$this->pastel, 2]]);                      // 200
        $this->pedido('2026-09-10 09:00', EstadoPedido::ENTREGADO, [[$this->pastel, 1], [$this->galletas, 3]]); // 160
        $this->pedido('2026-09-12 15:00', EstadoPedido::EN_PROCESO, [[$this->galletas, 5]]);                    // 100, no es venta
        $this->pedido('2026-09-13 15:00', EstadoPedido::CANCELADO, [[$this->pastel, 4]]);                       // 400, no es venta
        $this->pedido('2026-07-01 10:00', EstadoPedido::ENTREGADO, [[$this->pastel, 9]]);                       // fuera del período
    }

    public function test_solo_el_emprendedor_accede_a_los_reportes(): void
    {
        $this->get('/reportes')->assertRedirect('/login');
        $this->actingAs(Usuario::factory()->cliente()->create())->get('/reportes')->assertForbidden();
        $this->actingAs(Usuario::factory()->administrador()->create())->get('/reportes')->assertForbidden();
        $this->actingAs($this->emprendedor)->get('/reportes')->assertOk()->assertSee('Productos más vendidos');
    }

    public function test_resume_pedidos_y_ventas_del_periodo(): void
    {
        $this->datosDeEjemplo();

        $respuesta = $this->actingAs($this->emprendedor)->get('/reportes?desde=2026-09-01&hasta=2026-09-15')->assertOk();

        $resumen = $respuesta->viewData('resumen');
        $this->assertSame(4, $resumen['pedidos']);
        $this->assertSame(2, $resumen['ventas']);
        $this->assertSame(1, $resumen['en_curso']);
        $this->assertSame(1, $resumen['cancelados']);
        $this->assertEqualsWithDelta(360.0, $resumen['monto_vendido'], 0.001);
        $this->assertEqualsWithDelta(180.0, $resumen['ticket_promedio'], 0.001);
    }

    public function test_ventas_por_dia_incluye_los_dias_sin_ventas(): void
    {
        $this->datosDeEjemplo();

        $ventas = $this->actingAs($this->emprendedor)->get('/reportes?desde=2026-09-01&hasta=2026-09-15')->viewData('ventas');

        $this->assertCount(15, $ventas);
        $this->assertEqualsWithDelta(200.0, $ventas[0]['monto'], 0.001);
        $this->assertEqualsWithDelta(160.0, $ventas[9]['monto'], 0.001);
        $this->assertSame(0.0, $ventas[11]['monto']); // el pedido en proceso del día 12 no es venta
        $this->assertEqualsWithDelta(360.0, $ventas->sum('monto'), 0.001);
    }

    public function test_periodos_largos_se_agrupan_por_mes(): void
    {
        $this->datosDeEjemplo();

        $respuesta = $this->actingAs($this->emprendedor)->get('/reportes?desde=2026-06-01&hasta=2026-09-15');

        $this->assertTrue($respuesta->viewData('porMes'));
        $ventas = $respuesta->viewData('ventas');
        $this->assertCount(4, $ventas);
        $this->assertEqualsWithDelta(900.0, $ventas[1]['monto'], 0.001); // julio
        $this->assertEqualsWithDelta(360.0, $ventas[3]['monto'], 0.001); // septiembre
    }

    public function test_productos_mas_vendidos_solo_cuenta_pedidos_entregados(): void
    {
        $this->datosDeEjemplo();

        $productos = $this->actingAs($this->emprendedor)->get('/reportes?desde=2026-09-01&hasta=2026-09-15')->viewData('productos');

        $this->assertSame(['Pastel', 'Galletas'], $productos->pluck('nombre')->all());
        $this->assertSame(3, $productos[0]['unidades']);
        $this->assertEqualsWithDelta(300.0, $productos[0]['monto'], 0.001);
        $this->assertSame(3, $productos[1]['unidades']);
    }

    public function test_el_filtro_de_estado_aplica_al_detalle(): void
    {
        $this->datosDeEjemplo();

        $respuesta = $this->actingAs($this->emprendedor)
            ->get('/reportes?desde=2026-09-01&hasta=2026-09-15&estado='.EstadoPedido::ENTREGADO);

        $this->assertSame(2, $respuesta->viewData('pedidos')->total());
        $this->assertSame(4, $respuesta->viewData('resumen')['pedidos']);
    }

    public function test_sin_fechas_muestra_los_ultimos_30_dias(): void
    {
        $filtros = $this->actingAs($this->emprendedor)->get('/reportes')->viewData('filtros');

        $this->assertSame('2026-08-17', $filtros['desde']);
        $this->assertSame('2026-09-15', $filtros['hasta']);
    }

    public function test_rechaza_filtros_invalidos(): void
    {
        $this->actingAs($this->emprendedor);

        $this->get('/reportes?desde=2026-09-10&hasta=2026-09-01')->assertRedirect('/reportes')->assertSessionHasErrors('hasta');
        $this->get('/reportes?desde=10-09-2026')->assertRedirect('/reportes')->assertSessionHasErrors('desde');
        $this->get('/reportes?estado=99')->assertRedirect('/reportes')->assertSessionHasErrors('estado');
        $this->get('/reportes?desde=2024-01-01&hasta=2026-09-15')->assertRedirect('/reportes')->assertSessionHasErrors('desde');
    }

    public function test_no_incluye_datos_de_otro_emprendedor(): void
    {
        $this->datosDeEjemplo();
        $otro = Usuario::factory()->emprendedor()->create();
        $ajeno = ['id_emprendedor' => $otro->id_usuario];
        $productoAjeno = Producto::factory()->create($ajeno + ['nombre' => 'Tamales', 'precio' => 500]);
        $clienteAjeno = Cliente::factory()->create($ajeno + ['nombre' => 'Cliente ajeno']);
        $this->pedido('2026-09-05 10:00', EstadoPedido::ENTREGADO, [[$productoAjeno, 1]], $otro, $clienteAjeno);

        $respuesta = $this->actingAs($this->emprendedor)->get('/reportes?desde=2026-09-01&hasta=2026-09-15');

        $this->assertEqualsWithDelta(360.0, $respuesta->viewData('resumen')['monto_vendido'], 0.001);
        $respuesta->assertDontSee('Tamales')->assertDontSee('Cliente ajeno');
    }

    public function test_exporta_el_detalle_en_csv(): void
    {
        $this->datosDeEjemplo();
        $this->cliente->update(['nombre' => '=HYPERLINK("x")']);

        $respuesta = $this->actingAs($this->emprendedor)->get('/reportes/exportar?desde=2026-09-01&hasta=2026-09-15');

        $respuesta->assertOk()->assertDownload('reporte-pedidos_20260901_20260915.csv');
        $csv = $respuesta->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertSame(5, count(array_filter(explode("\n", trim($csv))))); // encabezado + 4 pedidos
        $this->assertStringContainsString('Entregado', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv); // no se exporta como fórmula
        $this->assertStringNotContainsString('2026-07', $csv);
    }
}
