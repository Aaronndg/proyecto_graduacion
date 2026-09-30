<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** RF-06 / RF-08 / RN-02: un pedido debe tener cliente, fecha y al menos un producto. */
class PedidoRequest extends FormRequest
{
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
            'fecha' => ['required', 'date'],
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
