<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use App\Support\WhatsApp;
use Illuminate\View\View;

/**
 * Catálogo para compartir (plus del proyecto): página pública del negocio con sus productos activos.
 * El cliente elige y envía su pedido por WhatsApp; el emprendedor lo registra en NEXO como siempre.
 */
class CatalogoController extends Controller
{
    public function show(string $catalogo): View
    {
        $negocio = Usuario::where('catalogo', $catalogo)->where('id_rol', Rol::EMPRENDEDOR)->where('activo', true)->firstOrFail();

        // Página pública: el filtro por emprendedor se aplica a mano (no hay sesión).
        $productos = Producto::withoutGlobalScope('emprendedor')
            ->where('id_emprendedor', $negocio->id_usuario)
            ->activos()
            ->orderBy('nombre')
            ->get(['id_producto', 'nombre', 'descripcion', 'precio', 'imagen']);

        return view('catalogo.show', [
            'negocio' => $negocio,
            'productos' => $productos,
            'whatsapp' => WhatsApp::numero($negocio->telefono),
        ]);
    }
}
