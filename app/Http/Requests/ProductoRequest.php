<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** RF-12 / RF-15: validación de los datos del producto (incluida su foto opcional). */
class ProductoRequest extends FormRequest
{
    public const IMAGEN_MAX_KB = 2048;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['estado' => $this->boolean('estado'), 'quitar_imagen' => $this->boolean('quitar_imagen')]);
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
            // Foto: JPG, PNG o WebP de hasta 2 MB (el tipo se verifica por el contenido, no solo la extensión).
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::IMAGEN_MAX_KB],
            'quitar_imagen' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya tiene un producto registrado con este nombre.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La foto debe ser JPG, PNG o WebP.',
            'imagen.max' => 'La foto no debe pesar más de 2 MB.',
            'imagen.uploaded' => 'No se pudo subir la foto. Pruebe con una imagen de menos de 2 MB.',
        ];
    }
}
