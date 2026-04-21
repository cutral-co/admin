<?php

namespace App\Http\Requests\DomicilioElectronico;

use App\Http\Requests\TraitRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class DomicilioRequest extends FormRequest
{

    use TraitRequest;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(Request $request)
    {
        if ($request->method == 'POST') {
            return [
                'email' => 'required|email',
                'domicilio_real' => 'required',
                'phone' => 'required',
                'nombre' => 'required',
                'documento' => 'required',
            ];
        } else {
            return [
                'email' => 'required|email',
                'domicilio_real' => 'required',
                'phone' => 'required',
            ];
        }
    }

    public function messages()
    {
        return [
            'email.required' => 'Correo es requerido',
            'domicilio_real.required' => 'El domicilio es requerido',
            'email.email' => 'Correo es inválido',
            'phone.required' => 'El teléfono es requerido',
            'nombre.required' => 'El nombre es requerido',
            'documento.required' => 'El documento es requerido',
            'renaper_id.required' => 'El número de trámite es requerido'
        ];
    }
}
