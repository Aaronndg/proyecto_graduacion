<?php

namespace App\Http\Controllers;

use App\Http\Requests\PedidoRequest;
use App\Models\Cliente;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Servicios\GestorPedidos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/** MOD-03 / RF-06, RF-07, RF-08: registro, consulta y actualización de pedidos (Figuras 38 y 39). */
class PedidoController extends Controller
{
    public function __construct(private readonly GestorPedidos $gestor)
    {
    }

    /**
     * Lista de pedidos con pestañas: «activos» (predeterminada), «todos» o un estado concreto
     * (los enlaces «Ver los N» de Hoy usan el estado). En «todos», los activos van primero.
     */
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar'));
        $desde = $this->fecha($request->query('desde'));
        $hasta = $this->fecha($request->query('hasta'));
        $estados = EstadoPedido::orderBy('orden')->get()->keyBy('id_estado');

        $vista = (string) $request->query('estado', 'activos');
        if (! in_array($vista, ['activos', 'todos'], true) && ! $estados->has((int) $vista)) {
            $vista = 'activos';
        }

        $base = Pedido::query()
            ->when($buscar !== '', function ($q) use ($buscar) {
                $numero = ltrim($buscar, '#0');
                $q->where(fn ($q) => $q
                    ->whereHas('cliente', fn ($c) => $c->where('nombre', 'like', "%{$buscar}%"))
                    ->when(ctype_digit($numero), fn ($q) => $q->orWhere('id_pedido', (int) $numero)));
            })
            ->when($desde, fn ($q) => $q->where('fecha', '>=', $desde->startOfDay()))
            ->when($hasta, fn ($q) => $q->where('fecha', '<=', $hasta->endOfDay()));

        // Número de cada pestaña con la búsqueda y las fechas aplicadas.
        $porEstado = (clone $base)->selectRaw('id_estado, count(*) as total')->groupBy('id_estado')->pluck('total', 'id_estado');
        $conteos = [
            'activos' => (int) collect(EstadoPedido::ACTIVOS)->sum(fn ($id) => $porEstado[$id] ?? 0),
            EstadoPedido::ENTREGADO => (int) ($porEstado[EstadoPedido::ENTREGADO] ?? 0),
            EstadoPedido::CANCELADO => (int) ($porEstado[EstadoPedido::CANCELADO] ?? 0),
            'todos' => (int) $porEstado->sum(),
        ];

        $pedidos = $base
            ->with(['cliente', 'estado'])
            ->withSum('detalles as unidades', 'cantidad')
            ->when($vista === 'activos', fn ($q) => $q->whereIn('id_estado', EstadoPedido::ACTIVOS))
            ->when(is_numeric($vista), fn ($q) => $q->where('id_estado', (int) $vista))
            ->when($vista === 'todos', fn ($q) => $q->orderByRaw('id_estado in (?, ?)', EstadoPedido::FINALES))
            ->latest('fecha')
            ->latest('id_pedido')
            ->paginate(15)
            ->withQueryString();

        return view('pedidos.index', [
            'pedidos' => $pedidos,
            'estados' => $estados,
            'conteos' => $conteos,
            'vista' => $vista,
            'filtros' => ['buscar' => $buscar, 'desde' => $desde?->toDateString(), 'hasta' => $hasta?->toDateString()],
        ]);
    }

    public function create(Request $request): View
    {
        $pedido = new Pedido(['fecha' => now()]);

        if ($request->filled('cliente')) {
            $pedido->id_cliente = Cliente::find($request->integer('cliente'))?->id_cliente;
        }

        return view('pedidos.formulario', $this->datosFormulario($pedido));
    }

    public function store(PedidoRequest $request): RedirectResponse
    {
        $pedido = $this->gestor->registrar($request->validated(), $request->user());

        return redirect()->route('pedidos.show', $pedido)->with('exito', "Pedido #{$pedido->numero()} registrado correctamente.");
    }

    public function show(Pedido $pedido): View
    {
        $pedido->load(['cliente', 'estado', 'detalles.producto', 'historial.estado', 'historial.usuario']);

        return view('pedidos.show', [
            'pedido' => $pedido,
            'editable' => $this->gestor->esEditable($pedido),
            'estadosSiguientes' => $this->gestor->estadosSiguientes($pedido),
        ]);
    }

    /** RF-09 / HU-04: actualización del estado del pedido. */
    public function cambiarEstado(Request $request, Pedido $pedido): RedirectResponse
    {
        $datos = $request->validate([
            'id_estado' => ['required', 'integer'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        $this->gestor->cambiarEstado($pedido, (int) $datos['id_estado'], $datos['observacion'] ?? null, $request->user());

        $estado = EstadoPedido::find($datos['id_estado']);

        return back()->with('exito', "Pedido #{$pedido->numero()} actualizado a «{$estado->nombre}».");
    }

    public function edit(Pedido $pedido): View|RedirectResponse
    {
        if (! $this->gestor->esEditable($pedido)) {
            return redirect()->route('pedidos.show', $pedido)->with('error', 'Un pedido entregado o cancelado ya no puede modificarse.');
        }

        $pedido->load('detalles');

        return view('pedidos.formulario', $this->datosFormulario($pedido));
    }

    public function update(PedidoRequest $request, Pedido $pedido): RedirectResponse
    {
        $this->gestor->actualizar($pedido, $request->validated());

        return redirect()->route('pedidos.show', $pedido)->with('exito', 'Pedido actualizado correctamente.');
    }

    private function datosFormulario(Pedido $pedido): array
    {
        $enPedido = $pedido->exists ? $pedido->detalles->pluck('id_producto') : collect();

        // Productos activos, más los ya incluidos en el pedido aunque se hayan desactivado.
        $productos = Producto::where(fn ($q) => $q->where('estado', true)->orWhereIn('id_producto', $enPedido))
            ->orderBy('nombre')
            ->get(['id_producto', 'nombre', 'precio', 'estado']);

        $lineas = old('productos', $pedido->exists
            ? $pedido->detalles->map(fn ($d) => ['id_producto' => $d->id_producto, 'cantidad' => $d->cantidad])->all()
            : [['id_producto' => '', 'cantidad' => 1]]);

        return [
            'pedido' => $pedido,
            'clientes' => Cliente::orderBy('nombre')->get(['id_cliente', 'nombre', 'telefono']),
            'productos' => $productos,
            // Precios con los que se registró el pedido (se conservan al editar).
            'preciosRegistrados' => $pedido->exists ? $pedido->detalles->pluck('precio_unitario', 'id_producto') : collect(),
            'lineas' => $lineas ?: [['id_producto' => '', 'cantidad' => 1]],
        ];
    }

    private function fecha(?string $valor): ?Carbon
    {
        try {
            return $valor ? Carbon::createFromFormat('Y-m-d', $valor) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
