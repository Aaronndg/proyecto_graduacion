<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** RF-05 / RF-15: validación de los datos del cliente. */
class ClienteRequest extends FormRequest
{
    public const TELEFONO = 'regex:/^[0-9+\-\s()]{8,20}$/';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('correo')) {
            $this->merge(['correo' => mb_strtolower(trim($this->input('correo')))]);
        }
    }

    public function rules(): array
    {
        return self::reglas($this->user()->id_usuario, $this->route('cliente')?->id_cliente);
    }

    /** Reglas compartidas con el registro rápido de clientes desde el formulario de pedido. */
    public static function reglas(int $idEmprendedor, ?int $ignorarCliente = null, string $prefijo = ''): array
    {
        return [
            $prefijo.'nombre' => ['required', 'string', 'max:100'],
            $prefijo.'telefono' => ['nullable', 'string', 'max:20', self::TELEFONO],
            $prefijo.'correo' => [
                'nullable', 'email', 'max:150',
                Rule::unique('clientes', 'correo')->where('id_emprendedor', $idEmprendedor)->ignore($ignorarCliente, 'id_cliente'),
            ],
            $prefijo.'direccion' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'telefono.regex' => 'El teléfono solo puede contener números, espacios, guiones o el signo +, con al menos 8 dígitos.',
            'correo.unique' => 'Ya tiene un cliente registrado con este correo electrónico.',
        ];
    }
}
