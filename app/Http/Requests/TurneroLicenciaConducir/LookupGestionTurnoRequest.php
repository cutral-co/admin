<?php

namespace App\Http\Requests\TurneroLicenciaConducir;

use App\Http\Requests\TraitRequest;
use Illuminate\Foundation\Http\FormRequest;

class LookupGestionTurnoRequest extends FormRequest
{
    use TraitRequest;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dni' => ['required', 'string', 'regex:/^[0-9]{8}$/'],
            'codigo_verificacion' => ['required', 'string', 'size:6', 'regex:/^[A-Z0-9]{6}$/'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'codigo_verificacion' => strtoupper((string) $this->input('codigo_verificacion')),
        ]);
    }

    public function messages(): array
    {
        return [
            'dni.required' => 'El DNI es requerido',
            'dni.regex' => 'El DNI debe tener 8 dígitos',
            'codigo_verificacion.required' => 'El código de verificación es requerido',
            'codigo_verificacion.size' => 'El código de verificación debe tener 6 caracteres',
            'codigo_verificacion.regex' => 'El código de verificación es inválido',
        ];
    }
}
