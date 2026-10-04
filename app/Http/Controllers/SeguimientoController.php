<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/**
 * MOD-04: el tablero de seguimiento forma parte de la pantalla «Hoy» (panel del emprendedor).
 * La ruta se conserva para que los enlaces y marcadores antiguos sigan funcionando.
 */
class SeguimientoController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return redirect()->route('panel');
    }
}
