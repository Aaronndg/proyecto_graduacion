<?php

namespace App\Servicios;

use App\Models\EstadoPedido;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Libro de Excel del reporte (RF-24) con el diseño de NEXO: encabezado oscuro con dorado,
 * filas alternadas, estados con su color, totales y la fila de títulos fija al desplazarse.
 * Los textos escritos por el usuario se guardan siempre como texto, nunca como fórmula.
 */
class LibroReporte
{
    private const OSCURO = '1A1B20';
    private const ORO = 'E0B04F';
    private const ORO_CLARO = 'FBF3E1';
    private const ALTERNA = 'F7F5F0';
    private const LINEA = 'E4DFD3';
    private const GRIS = '6B675E';
    private const MONEDA = '"Q"#,##0.00';

    /** Color de fondo y de texto de cada estado, iguales a los de la aplicación. */
    private const ESTADOS = [
        EstadoPedido::NUEVO => ['DCEBFB', '1D4E89'],
        EstadoPedido::EN_PROCESO => ['FCEFD6', '8A5A00'],
        EstadoPedido::LISTO => ['DDF3E6', '1F6B3D'],
        EstadoPedido::ENTREGADO => ['ECEAE4', '4A4740'],
        EstadoPedido::CANCELADO => ['FBE0DE', '9B2C24'],
    ];

    private Writer $libro;

    private Options $opciones;

    private int $hojaActual = 0;

    private int $fila = 0;

    /**
     * @param  array<string, mixed>  $resumen
     * @param  Collection<int, array{estado: EstadoPedido, cantidad: int}>  $porEstado
     * @param  Collection<int, array{nombre: string, unidades: int|float, monto: float}>  $productos
     * @param  iterable<array{pedido: string, fecha: \Illuminate\Support\Carbon, cliente: string, estado: string, id_estado?: int, unidades: int, total: float}>  $pedidos
     */
    public function escribir(string $destino, string $negocio, string $periodo, array $resumen, Collection $porEstado, Collection $productos, iterable $pedidos): void
    {
        $this->opciones = new Options;
        $this->libro = new Writer($this->opciones);
        $this->libro->openToFile($destino);

        $this->hojaResumen($negocio, $periodo, $resumen, $porEstado);
        $this->hojaPedidos($negocio, $periodo, $pedidos);
        $this->hojaProductos($negocio, $periodo, $productos, (float) $resumen['monto_vendido']);

        $this->libro->close();
    }

    // ───────── Hojas ─────────

    private function hojaResumen(string $negocio, string $periodo, array $resumen, Collection $porEstado): void
    {
        $hoja = $this->libro->getCurrentSheet();
        $hoja->setName('Resumen');
        $hoja->setSheetView((new SheetView)->setShowGridLines(false));
        $hoja->setColumnWidth(36, 1);
        $hoja->setColumnWidth(18, 2);
        $hoja->setColumnWidth(16, 3);
        $this->fila = 0;

        $this->encabezado('Reporte de pedidos y ventas', $negocio, $periodo, 3);

        $this->titulo('Indicadores del período', 3);
        $this->filaEncabezado(['Indicador', 'Valor', '']);
        $indicadores = [
            ['Pedidos del período', $resumen['pedidos'], null],
            ['Ventas (pedidos entregados)', $resumen['ventas'], null],
            ['Pedidos en curso', $resumen['en_curso'], null],
            ['Pedidos cancelados', $resumen['cancelados'], null],
            ['Monto vendido', (float) $resumen['monto_vendido'], self::MONEDA],
            ['Venta promedio', round((float) $resumen['ticket_promedio'], 2), self::MONEDA],
        ];
        foreach ($indicadores as $i => [$texto, $valor, $formato]) {
            $destacado = $formato !== null;
            $this->agregar(new Row([
                Cell::fromValue($texto, $this->celda($i, negrita: $destacado)),
                Cell::fromValue($valor, $this->celda($i, formato: $formato, negrita: $destacado, alineacion: CellAlignment::RIGHT)),
                Cell::fromValue('', $this->celda($i)),
            ], null), 20);
        }

        $this->vacia();
        $this->titulo('Pedidos por estado', 3);
        $this->filaEncabezado(['Estado', 'Pedidos', 'Porcentaje']);
        $total = max(1, (int) $resumen['pedidos']);
        foreach ($porEstado->values() as $i => $fila) {
            $this->agregar(new Row([
                Cell::fromValue($fila['estado']->nombre, $this->estiloEstado($fila['estado']->id_estado)),
                Cell::fromValue($fila['cantidad'], $this->celda($i, alineacion: CellAlignment::RIGHT)),
                Cell::fromValue($fila['cantidad'] / $total, $this->celda($i, formato: '0.0%', alineacion: CellAlignment::RIGHT)),
            ], null), 20);
        }
        $this->filaTotal(['Total', (int) $resumen['pedidos'], 1], [null, null, '0.0%']);

        $this->vacia();
        $this->agregar(new Row([Cell::fromValue('Solo los pedidos entregados cuentan como venta.', (new Style)->setFontItalic()->setFontSize(9)->setFontColor(self::GRIS))], null));
    }

    private function hojaPedidos(string $negocio, string $periodo, iterable $pedidos): void
    {
        $hoja = $this->libro->addNewSheetAndMakeItCurrent();
        $this->hojaActual = 1;
        $this->fila = 0;
        $hoja->setName('Pedidos');
        $hoja->setColumnWidth(11, 1);
        $hoja->setColumnWidth(18, 2);
        $hoja->setColumnWidth(32, 3);
        $hoja->setColumnWidth(15, 4);
        $hoja->setColumnWidth(12, 5);
        $hoja->setColumnWidth(15, 6);

        $this->encabezado('Detalle de pedidos', $negocio, $periodo, 6, conGenerado: false);
        $filaTitulos = $this->filaEncabezado(['Pedido', 'Fecha', 'Cliente', 'Estado', 'Productos', 'Total']);
        $hoja->setSheetView((new SheetView)->setShowGridLines(false)->setFreezeRow($filaTitulos + 1));

        $unidades = 0;
        $monto = 0.0;
        $i = 0;
        foreach ($pedidos as $p) {
            $this->agregar(new Row([
                Cell::fromValue($p['pedido'], $this->celda($i, negrita: true, color: '8F6410')),
                Cell::fromValue($p['fecha']->toDateTimeImmutable(), $this->celda($i, formato: 'dd/mm/yyyy hh:mm', alineacion: CellAlignment::LEFT)),
                new Cell\StringCell($p['cliente'], $this->celda($i)), // nunca como fórmula
                Cell::fromValue($p['estado'], $this->estiloEstado($p['id_estado'] ?? null)),
                Cell::fromValue($p['unidades'], $this->celda($i, alineacion: CellAlignment::RIGHT)),
                Cell::fromValue($p['total'], $this->celda($i, formato: self::MONEDA, alineacion: CellAlignment::RIGHT)),
            ], null), 19);
            $unidades += $p['unidades'];
            $monto += $p['total'];
            $i++;
        }

        if ($i > 0) {
            $hoja->setAutoFilter(new AutoFilter(0, $filaTitulos, 5, $this->fila));
        }
        $this->filaTotal(["{$i} pedidos", '', '', '', $unidades, round($monto, 2)], [null, null, null, null, null, self::MONEDA]);
    }

    private function hojaProductos(string $negocio, string $periodo, Collection $productos, float $vendido): void
    {
        $hoja = $this->libro->addNewSheetAndMakeItCurrent();
        $this->hojaActual = 2;
        $this->fila = 0;
        $hoja->setName('Productos más vendidos');
        $hoja->setColumnWidth(6, 1);
        $hoja->setColumnWidth(34, 2);
        $hoja->setColumnWidth(12, 3);
        $hoja->setColumnWidth(15, 4);
        $hoja->setColumnWidth(15, 5);

        $this->encabezado('Productos más vendidos', $negocio, $periodo, 5, conGenerado: false);
        $filaTitulos = $this->filaEncabezado(['#', 'Producto', 'Unidades', 'Monto', 'Participación']);
        $hoja->setSheetView((new SheetView)->setShowGridLines(false)->setFreezeRow($filaTitulos + 1));

        $unidades = 0;
        $monto = 0.0;
        foreach ($productos->values() as $i => $producto) {
            $primero = $i === 0;
            $this->agregar(new Row([
                Cell::fromValue($i + 1, $this->celda($i, negrita: true, color: '8F6410', alineacion: CellAlignment::CENTER, fondo: $primero ? self::ORO_CLARO : null)),
                new Cell\StringCell($producto['nombre'], $this->celda($i, negrita: $primero, fondo: $primero ? self::ORO_CLARO : null)),
                Cell::fromValue((int) $producto['unidades'], $this->celda($i, alineacion: CellAlignment::RIGHT, fondo: $primero ? self::ORO_CLARO : null)),
                Cell::fromValue((float) $producto['monto'], $this->celda($i, formato: self::MONEDA, alineacion: CellAlignment::RIGHT, fondo: $primero ? self::ORO_CLARO : null)),
                Cell::fromValue($vendido > 0 ? $producto['monto'] / $vendido : 0, $this->celda($i, formato: '0.0%', alineacion: CellAlignment::RIGHT, fondo: $primero ? self::ORO_CLARO : null)),
            ], null), 19);
            $unidades += (int) $producto['unidades'];
            $monto += (float) $producto['monto'];
        }
        if ($productos->isEmpty()) {
            $this->agregar(new Row([Cell::fromValue(''), Cell::fromValue('No hay ventas entregadas en este período.', (new Style)->setFontItalic()->setFontColor(self::GRIS))], null));
        } else {
            $this->filaTotal(['', 'Total', $unidades, round($monto, 2), $vendido > 0 ? $monto / $vendido : 0], [null, null, null, self::MONEDA, '0.0%']);
        }
    }

    // ───────── Piezas comunes ─────────

    /** Franja oscura con el nombre del reporte y una franja dorada clara con el negocio y el período. */
    private function encabezado(string $titulo, string $negocio, string $periodo, int $columnas, bool $conGenerado = true): void
    {
        $oscuro = (new Style)->setFontBold()->setFontSize(16)->setFontColor('FFFFFF')->setBackgroundColor(self::OSCURO)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
        $this->agregar(new Row(array_merge([Cell::fromValue('NEXO · '.$titulo, $oscuro)], $this->relleno($columnas - 1, $oscuro)), null), 32);
        $this->unir($columnas);

        $claro = (new Style)->setFontBold()->setFontColor('3B3627')->setBackgroundColor(self::ORO_CLARO)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder(new Border(new BorderPart(Border::BOTTOM, self::ORO, Border::WIDTH_MEDIUM)));
        $texto = $negocio.'  ·  '.$periodo.($conGenerado ? '' : '  ·  Generado el '.now()->format('d/m/Y H:i'));
        $this->agregar(new Row(array_merge([new Cell\StringCell($texto, $claro)], $this->relleno($columnas - 1, $claro)), null), 22);
        $this->unir($columnas);

        if ($conGenerado) {
            $this->agregar(new Row([Cell::fromValue('Generado el '.now()->format('d/m/Y H:i'), (new Style)->setFontItalic()->setFontSize(9)->setFontColor(self::GRIS))], null), 16);
            $this->vacia();
        }
    }

    private function titulo(string $texto, int $columnas): void
    {
        $estilo = (new Style)->setFontBold()->setFontSize(12)->setFontColor(self::OSCURO)
            ->setBorder(new Border(new BorderPart(Border::BOTTOM, self::ORO, Border::WIDTH_THIN)));
        $this->agregar(new Row(array_merge([Cell::fromValue($texto, $estilo)], $this->relleno($columnas - 1, $estilo)), null), 22);
    }

    /** @return int número de fila de los títulos (para fijarla y filtrar) */
    private function filaEncabezado(array $titulos): int
    {
        $celdas = [];
        foreach ($titulos as $k => $titulo) {
            $alinear = $k === 0 || ! in_array($titulo, ['Valor', 'Pedidos', 'Porcentaje', 'Productos', 'Total', 'Unidades', 'Monto', 'Participación'], true)
                ? CellAlignment::LEFT : CellAlignment::RIGHT;
            $celdas[] = Cell::fromValue($titulo, (new Style)->setFontBold()->setFontColor('FFFFFF')->setBackgroundColor(self::OSCURO)
                ->setCellAlignment($titulo === '#' ? CellAlignment::CENTER : $alinear)->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
                ->setBorder(new Border(new BorderPart(Border::BOTTOM, self::ORO, Border::WIDTH_MEDIUM))));
        }
        $this->agregar(new Row($celdas, null), 24);

        return $this->fila;
    }

    private function filaTotal(array $valores, array $formatos): void
    {
        $celdas = [];
        foreach ($valores as $k => $valor) {
            $estilo = (new Style)->setFontBold()->setFontColor(self::OSCURO)->setBackgroundColor(self::ORO_CLARO)
                ->setBorder(new Border(new BorderPart(Border::TOP, self::ORO, Border::WIDTH_MEDIUM)))
                ->setCellAlignment($k === 0 || ! is_numeric($valor) ? CellAlignment::LEFT : CellAlignment::RIGHT);
            if ($formatos[$k] ?? null) {
                $estilo->setFormat($formatos[$k]);
            }
            $celdas[] = is_string($valor) ? new Cell\StringCell($valor, $estilo) : Cell::fromValue($valor, $estilo);
        }
        $this->agregar(new Row($celdas, null), 22);
    }

    private function celda(int $indice, ?string $formato = null, bool $negrita = false, string $alineacion = CellAlignment::LEFT, ?string $color = null, ?string $fondo = null): Style
    {
        $estilo = (new Style)->setCellAlignment($alineacion)->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder(new Border(new BorderPart(Border::BOTTOM, self::LINEA, Border::WIDTH_THIN)));
        $fondo ??= $indice % 2 ? self::ALTERNA : null;
        if ($fondo) {
            $estilo->setBackgroundColor($fondo);
        }
        if ($formato) {
            $estilo->setFormat($formato);
        }
        if ($negrita) {
            $estilo->setFontBold();
        }
        if ($color) {
            $estilo->setFontColor($color);
        }

        return $estilo;
    }

    private function estiloEstado(?int $estado): Style
    {
        [$fondo, $texto] = self::ESTADOS[$estado] ?? ['FFFFFF', self::OSCURO];

        return (new Style)->setFontBold()->setFontColor($texto)->setBackgroundColor($fondo)
            ->setCellAlignment(CellAlignment::CENTER)->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder(new Border(new BorderPart(Border::BOTTOM, 'FFFFFF', Border::WIDTH_THIN)));
    }

    /** @return Cell[] */
    private function relleno(int $cantidad, Style $estilo): array
    {
        return array_map(fn () => Cell::fromValue('', $estilo), range(1, max(1, $cantidad)));
    }

    private function unir(int $columnas): void
    {
        $this->opciones->mergeCells(0, $this->fila, $columnas - 1, $this->fila, $this->hojaActual);
    }

    private function vacia(): void
    {
        $this->agregar(new Row([], null), 8);
    }

    private function agregar(Row $fila, ?float $alto = null): void
    {
        if ($alto) {
            $fila->setHeight($alto);
        }
        $this->libro->addRow($fila);
        $this->fila++;
    }
}
