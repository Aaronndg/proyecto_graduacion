<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoRequest;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** RF-12: gestión del catálogo de productos del emprendedor (Figura 37). */
class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar'));
        $estado = $request->query('estado');

        $productos = Producto::query()
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('nombre', 'like', "%{$buscar}%")
                ->orWhere('descripcion', 'like', "%{$buscar}%")))
            ->when(in_array($estado, ['activos', 'inactivos'], true), fn ($q) => $q->where('estado', $estado === 'activos'))
            ->orderByDesc('estado')
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('productos.index', compact('productos', 'buscar', 'estado'));
    }

    public function create(): View
    {
        return view('productos.formulario', ['producto' => new Producto(['estado' => true])]);
    }

    public function store(ProductoRequest $request): RedirectResponse
    {
        $producto = new Producto($request->safe()->except(['imagen', 'quitar_imagen']));
        $producto->cambiarImagen($request->file('imagen'));
        $producto->save();

        return redirect()->route('productos.index')->with('exito', 'Producto registrado correctamente.');
    }

    public function edit(Producto $producto): View
    {
        return view('productos.formulario', compact('producto'));
    }

    public function update(ProductoRequest $request, Producto $producto): RedirectResponse
    {
        $producto->fill($request->safe()->except(['imagen', 'quitar_imagen']));
        $producto->cambiarImagen($request->file('imagen'), $request->boolean('quitar_imagen'));
        $producto->save();

        return redirect()->route('productos.index')->with('exito', 'Producto actualizado correctamente.');
    }

    /** Activa o desactiva el producto con un toque desde el catálogo (sin abrir el formulario). */
    public function alternarDisponible(Producto $producto): RedirectResponse
    {
        $producto->update(['estado' => ! $producto->estado]);

        return back()->with('exito', $producto->estado
            ? "«{$producto->nombre}» está disponible otra vez."
            : "«{$producto->nombre}» ya no aparece al crear pedidos.");
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        // Un producto usado en pedidos se desactiva en lugar de eliminarse, para conservar el historial.
        if ($producto->detalles()->exists()) {
            return back()->with('error', 'Este producto forma parte de pedidos registrados. Desactívelo en lugar de eliminarlo.');
        }

        $producto->cambiarImagen(null, quitar: true);
        $producto->delete();

        return redirect()->route('productos.index')->with('exito', 'Producto eliminado.');
    }
}
