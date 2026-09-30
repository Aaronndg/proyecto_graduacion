<?php

namespace App\Http\Requests;

use App\Models\Pedido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** RF-06 / RF-08 / RN-02: un pedido debe tener cliente, fecha y al menos un producto. */
class PedidoRequest extends FormRequest
{
    /**
     * Rango aceptado para la fecha del pedido, relativo a hoy: evita fechas escritas por error
     * (p. ej. 1926 o 2062) y no queda desactualizado con el paso de los años.
     */
    public const MESES_ATRAS = 12;
    public const MESES_A_FUTURO = 6;

    /** Al editar, un pedido activo con fecha más antigua que el límite conserva su fecha. */
    public static function fechaMinima(?Pedido $pedido = null): string
    {
        $minima = now()->subMonths(self::MESES_ATRAS)->startOfDay();

        if ($pedido?->fecha && $pedido->fecha->lt($minima)) {
            $minima = $pedido->fecha->copy()->startOfMinute();
        }

        return $minima->format('Y-m-d\TH:i');
    }

    public static function fechaMaxima(): string
    {
        return now()->addMonths(self::MESES_A_FUTURO)->endOfDay()->format('Y-m-d\TH:i');
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $productos = collect($this->input('productos', []))
            ->filter(fn ($linea) => is_array($linea) && filled($linea['id_producto'] ?? null))
            ->values()
            ->all();

        $nuevoCliente = $this->input('nuevo_cliente', []);
        if (is_array($nuevoCliente) && filled($nuevoCliente['correo'] ?? null)) {
            $nuevoCliente['correo'] = mb_strtolower(trim($nuevoCliente['correo']));
        }

        $this->merge([
            'productos' => $productos,
            'modo_cliente' => $this->input('modo_cliente', 'existente'),
            'nuevo_cliente' => $nuevoCliente,
        ]);
    }

    public function rules(): array
    {
        $idEmprendedor = $this->user()->id_usuario;
        $esNuevo = $this->input('modo_cliente') === 'nuevo';

        $reglas = [
            'modo_cliente' => ['required', Rule::in(['existente', 'nuevo'])],
            'id_cliente' => [
                Rule::requiredIf(! $esNuevo), 'nullable', 'integer',
                Rule::exists('clientes', 'id_cliente')->where('id_emprendedor', $idEmprendedor),
            ],
            'fecha' => ['required', 'date', 'after_or_equal:'.self::fechaMinima($this->route('pedido')), 'before_or_equal:'.self::fechaMaxima()],
            'productos' => ['required', 'array', 'min:1', 'max:50'],
            'productos.*.id_producto' => [
                'required', 'integer',
                Rule::exists('productos', 'id_producto')->where('id_emprendedor', $idEmprendedor),
            ],
            'productos.*.cantidad' => ['required', 'integer', 'min:1', 'max:9999'],
        ];

        if ($esNuevo) {
            $reglas += ClienteRequest::reglas($idEmprendedor, null, 'nuevo_cliente.');
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'id_cliente.required' => 'Seleccione el cliente del pedido.',
            'fecha.after_or_equal' => 'La fecha del pedido no puede ser de hace más de un año.',
            'fecha.before_or_equal' => 'La fecha del pedido no puede ser más de 6 meses en el futuro.',
            'productos.required' => 'Agregue al menos un producto al pedido.',
            'productos.min' => 'Agregue al menos un producto al pedido.',
            'productos.*.id_producto.exists' => 'Uno de los productos seleccionados no es válido.',
            'productos.*.cantidad.min' => 'La cantidad de cada producto debe ser al menos 1.',
            'productos.*.cantidad.*' => 'Indique una cantidad válida para cada producto.',
            'nuevo_cliente.telefono.regex' => 'El teléfono solo puede contener números, espacios, guiones o el signo +, con al menos 8 dígitos.',
            'nuevo_cliente.correo.unique' => 'Ya tiene un cliente registrado con este correo electrónico.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nuevo_cliente.nombre' => 'nombre del cliente',
            'nuevo_cliente.telefono' => 'teléfono del cliente',
            'nuevo_cliente.correo' => 'correo del cliente',
            'nuevo_cliente.direccion' => 'dirección del cliente',
        ];
    }
}
