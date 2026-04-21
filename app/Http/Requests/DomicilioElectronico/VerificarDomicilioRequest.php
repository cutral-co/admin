<?php

namespace App\Http\Requests\DomicilioElectronico;

use App\Http\Requests\TraitRequest;
use Illuminate\Foundation\Http\FormRequest;

class VerificarDomicilioRequest extends FormRequest
{
    use TraitRequest;

    public function rules()
    {
        return [
            'email' => 'required|string',
            'token' => 'required|string|size:60'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Es necesario informar un email',
            'email.string' => 'El email debe ser válido',
            'token.required' => 'Es necesario informar un token',
            'token.string' => 'El token debe ser alfanumérico',
            'token.size' => 'El token debe contener 60 caracteres'
        ];
    }
}
