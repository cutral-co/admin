<?php

namespace App\Http\Requests\DomicilioElectronico;

use App\Http\Requests\TraitRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class EnviarNotificacionRequest extends FormRequest
{
    use TraitRequest;

    public function authorize()
    {
        return true;
    }

    public function rules(Request $request)
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'origin' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'El título es requerido',
            'body.required' => 'El cuerpo es requerido',
            'origin.required' => 'El origen es requerido',
        ];
    }
}