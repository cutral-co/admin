<?php

namespace App\Http\Requests\TurneroLicenciaConducir;

use App\Http\Requests\TraitRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreSolicitudRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'telefono' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'dni.required' => 'El DNI es requerido',
            'dni.regex' => 'El DNI debe tener 8 dígitos',
            'nombre.required' => 'El nombre es requerido',
            'apellido.required' => 'El apellido es requerido',
            'telefono.required' => 'El teléfono es requerido',
            'email.required' => 'El correo electrónico es requerido',
            'email.email' => 'El correo electrónico es inválido',
        ];
    }
}
