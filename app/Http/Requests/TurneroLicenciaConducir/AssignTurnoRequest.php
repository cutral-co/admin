<?php

namespace App\Http\Requests\TurneroLicenciaConducir;

use App\Http\Requests\TraitRequest;
use Illuminate\Foundation\Http\FormRequest;

class AssignTurnoRequest extends FormRequest
{
    use TraitRequest;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_turno' => ['required', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_turno.required' => 'La fecha y hora del turno es requerida',
            'fecha_turno.date' => 'La fecha y hora del turno es inválida',
            'fecha_turno.after' => 'La fecha y hora del turno debe ser futura',
        ];
    }
}
