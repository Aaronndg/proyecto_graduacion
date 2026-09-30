<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** RF-12 / RF-15: validación de los datos del producto. */
class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['estado' => $this->boolean('estado')]);
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('productos', 'nombre')
                    ->where('id_emprendedor', $this->user()->id_usuario)
                    ->ignore($this->route('producto')?->id_producto, 'id_producto'),
            ],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'estado' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return ['nombre.unique' => 'Ya tiene un producto registrado con este nombre.'];
    }
}
