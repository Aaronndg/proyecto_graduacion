<?php

namespace App\Http\Requests;

use App\Servicios\Reportes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/** RF-13 / RF-15: filtros del reporte. Sin fechas, el reporte muestra los últimos 30 días. */
class ReporteRequest extends FormRequest
{
    /** Límite del período consultado, para mantener el reporte legible y rápido. */
    public const MAX_DIAS = 366;

    /** Con filtros inválidos se vuelve al reporte sin filtros (evita redirigir en bucle a la misma URL). */
    protected $redirectRoute = 'reportes';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'estado' => ['nullable', 'integer', Rule::exists('estados_pedido', 'id_estado')],
        ];
    }

    public function after(): array
    {
        return [function ($validator) {
            if ($validator->errors()->isEmpty() && $this->desde()->diffInDays($this->hasta()) > self::MAX_DIAS) {
                $validator->errors()->add('desde', 'El período del reporte no puede ser mayor a un año.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'desde.date_format' => 'La fecha inicial no es válida.',
            'hasta.date_format' => 'La fecha final no es válida.',
            'hasta.after_or_equal' => 'La fecha final debe ser igual o posterior a la inicial.',
            'estado.exists' => 'El estado seleccionado no es válido.',
        ];
    }

    public function desde(): Carbon
    {
        return $this->filled('desde')
            ? Carbon::createFromFormat('Y-m-d', $this->input('desde'))->startOfDay()
            : $this->hasta()->subDays(29)->startOfDay();
    }

    public function hasta(): Carbon
    {
        return $this->filled('hasta') ? Carbon::createFromFormat('Y-m-d', $this->input('hasta'))->startOfDay() : today();
    }

    public function reportes(): Reportes
    {
        return new Reportes($this->desde(), $this->hasta(), $this->integer('estado') ?: null);
    }
}
