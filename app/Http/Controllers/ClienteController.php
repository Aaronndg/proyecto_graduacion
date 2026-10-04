<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** MOD-02 / RF-05: gestión de clientes del emprendedor (Figura 36). */
class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar'));

        $clientes = Cliente::withCount('pedidos')
            ->withMax('pedidos as ultimo_pedido', 'fecha')
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('nombre', 'like', "%{$buscar}%")
                ->orWhere('telefono', 'like', "%{$buscar}%")
                ->orWhere('correo', 'like', "%{$buscar}%")))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('clientes.index', compact('clientes', 'buscar'));
    }

    public function create(): View
    {
        return view('clientes.formulario', ['cliente' => new Cliente()]);
    }

    public function store(ClienteRequest $request): RedirectResponse
    {
        $cliente = Cliente::create($request->validated());

        return redirect()->route('clientes.show', $cliente)->with('exito', 'Cliente registrado correctamente.');
    }

    public function show(Cliente $cliente): View
    {
        $cliente->load(['usuario']);
        $pedidos = $cliente->pedidos()->with('estado')->latest('fecha')->latest('id_pedido')->paginate(10);

        return view('clientes.show', compact('cliente', 'pedidos'));
    }

    public function edit(Cliente $cliente): View
    {
        return view('clientes.formulario', compact('cliente'));
    }

    public function update(ClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $cliente->update($request->validated());

        return redirect()->route('clientes.show', $cliente)->with('exito', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        // RN-08: no se eliminan clientes con pedidos, para conservar el historial.
        if ($cliente->pedidos()->exists()) {
            return back()->with('error', 'No se puede eliminar un cliente que tiene pedidos registrados.');
        }

        $cliente->delete();

        return redirect()->route('clientes.index')->with('exito', 'Cliente eliminado.');
    }
}
