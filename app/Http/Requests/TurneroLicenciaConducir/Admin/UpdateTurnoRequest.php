<?php

namespace App\Http\Requests\TurneroLicenciaConducir\Admin;

use App\Http\Requests\TraitRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTurnoRequest extends FormRequest
{
    use TraitRequest;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_turno' => ['required', 'date_format:Y-m-d H:i', 'after:now'],
            'email' => ['required', 'email', 'max:150'],
        ];
    }
}
