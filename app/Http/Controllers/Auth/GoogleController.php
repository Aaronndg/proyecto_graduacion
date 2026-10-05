<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Rol;
use App\Models\Usuario;
use App\Servicios\VinculacionClientes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as RedireccionExterna;

/**
 * «Continuar con Google» (RF-01): entrar o crear la cuenta con la cuenta de Google.
 * Si el correo ya tiene cuenta en NEXO, se entra a esa cuenta; si no, se pide solo lo que falta
 * (cómo va a usar NEXO y, si es negocio, su nombre). El código de invitación del cliente se conserva.
 */
class GoogleController extends Controller
{
    public function __construct(private readonly VinculacionClientes $vinculacion)
    {
    }

    public function redirigir(Request $request): RedireccionExterna
    {
        // Si llegó con su código de invitación, se guarda para vincularlo al volver de Google.
        $codigo = Cliente::normalizarCodigo((string) $request->query('codigo'));
        $request->session()->put('google_codigo', strlen($codigo) === 8 ? $codigo : null);

        return Socialite::driver('google')->redirect();
    }

    public function volver(Request $request): RedirectResponse
    {
        try {
            $google = Socialite::driver('google')->user();
        } catch (\Throwable) {
            return redirect()->route('login')->with('error', 'No pudimos entrar con Google. Intente otra vez.');
        }

        $correo = mb_strtolower(trim((string) $google->getEmail()));
        $usuario = Usuario::where('google_id', $google->getId())->first()
            ?? ($correo ? Usuario::where('correo', $correo)->first() : null);

        if (! $usuario) {
            $request->session()->put('google', ['id' => $google->getId(), 'nombre' => $google->getName() ?: Str::before($correo, '@'), 'correo' => $correo]);

            return redirect()->route('google.completar');
        }

        if (! $usuario->activo) {
            return redirect()->route('login')->with('error', __('auth.inactive'));
        }

        if (! $usuario->google_id) {
            $usuario->forceFill(['google_id' => $google->getId()])->save();
        }

        $mensaje = $this->vincularCodigoPendiente($request, $usuario);

        Auth::login($usuario, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('panel'))->with($mensaje ? ['exito' => $mensaje] : []);
    }

    public function completar(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('google')) {
            return redirect()->route('login');
        }

        return view('auth.google-completar', [
            'google' => $request->session()->get('google'),
            'codigo' => $request->session()->get('google_codigo'),
        ]);
    }

    public function guardar(Request $request): RedirectResponse
    {
        $google = $request->session()->get('google');
        if (! $google) {
            return redirect()->route('login');
        }

        $datos = $request->validate([
            'tipo' => ['required', Rule::in(['emprendedor', 'cliente'])],
            'nombre' => ['required', 'string', 'max:100'],
            'negocio' => ['nullable', 'required_if:tipo,emprendedor', 'string', 'max:100'],
            'codigo' => ['nullable', 'string', 'max:20'],
        ], ['negocio.required_if' => 'Escriba el nombre de su negocio.']);

        if (Usuario::where('correo', $google['correo'])->exists()) {
            return redirect()->route('login')->with('error', 'Ese correo ya tiene una cuenta. Entre con Google otra vez.');
        }

        $esEmprendedor = $datos['tipo'] === 'emprendedor';
        $codigo = $esEmprendedor ? null : ($datos['codigo'] ?? null);
        $cliente = $codigo ? $this->vinculacion->buscar($codigo, 'vincular-registro:'.$request->ip()) : null;

        $usuario = DB::transaction(function () use ($datos, $google, $esEmprendedor, $cliente) {
            $usuario = Usuario::create([
                'nombre' => $datos['nombre'],
                'correo' => $google['correo'],
                // Entra con Google: la contraseña es aleatoria. Si algún día la quiere, usa «¿Olvidó su contraseña?».
                'contrasena' => Str::random(40),
                'id_rol' => $esEmprendedor ? Rol::EMPRENDEDOR : Rol::CLIENTE,
                'negocio' => $esEmprendedor ? $datos['negocio'] : null,
            ]);
            $usuario->forceFill(['google_id' => $google['id']])->save();
            $cliente?->vincularCon($usuario);

            return $usuario;
        });

        $request->session()->forget(['google', 'google_codigo']);
        Auth::login($usuario, remember: true);
        $request->session()->regenerate();

        return redirect()->route('panel')->with('exito', $cliente ? 'Su cuenta está lista. Ya puede ver sus pedidos.' : 'Su cuenta está lista.');
    }

    /** Un cliente que ya tenía cuenta y llegó con un código de invitación queda vinculado de una vez. */
    private function vincularCodigoPendiente(Request $request, Usuario $usuario): ?string
    {
        $codigo = $request->session()->pull('google_codigo');
        if (! $codigo || ! $usuario->esCliente()) {
            return null;
        }

        try {
            $this->vinculacion->vincular($usuario, $codigo);

            return 'Listo. Ya puede ver sus pedidos de este negocio.';
        } catch (ValidationException) {
            return null;
        }
    }
}
