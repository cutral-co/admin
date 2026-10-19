<?php

namespace App\Http\Requests\TurneroLicenciaConducir;

use App\Http\Requests\TraitRequest;
use App\Models\TurneroLicenciaConducir\Solicitud;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSolicitudEstadoRequest extends FormRequest
{
    use TraitRequest;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => [
                'required',
                'string',
                Rule::in([...Solicitud::estadoTurnoOptions(), Solicitud::ESTADO_CANCELADO]),
            ],
            'motivo_cancelacion' => ['required_if:estado,cancelado', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'estado.required' => 'El estado es requerido',
            'estado.in' => 'El estado seleccionado es inválido',
            'motivo_cancelacion.required_if' => 'El motivo de cancelación es obligatorio',
        ];
    }
}
