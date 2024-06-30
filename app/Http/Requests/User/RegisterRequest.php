<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'lastname' => 'required|string|max:30',
            'name' => 'required|string|max:30',
            'cuit' => ['required', 'string', 'regex:/^(?!0)([0-9]){11}$/'],
            'email' => ['required', 'string', 'regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/', 'max:255'],
            'password' => [
                'required',
                'string',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[!@#$%^&*(),.?":{}|<>]/', // Un símbolo especial
                'min:7'
            ],
            'phone' => ['required', 'string', 'regex:/^\d+$/', 'min:10', 'max:20'],
        ];
    }

    public function messages()
    {
        return [
            'lastname.required' => 'Apellido es requerido',
            'lastname.max' => 'Apellido no debe superar los 30 caracteres',

            'name.required' => 'Nombre es requerido',
            'name.max' => 'Nombre no debe superar los 30 caracteres',

            'cuit.required' => 'CUIT/CUIL es requerido',
            'cuit.regex' => 'El CUIT/CUIL incorporado es inválido',

            'email.required' => 'El correo electronico es requerido',
            'email.regex' => 'Formato de correo incorrecto. Ejemplo: ejemplo@gmail.com',
            'email.max' => 'Máximo 255 caracteres',

            'password.required' => 'La contraseña es requerida',
            'password.regex' => 'La contraseña debe tener al menos: una mayúscula, una minúscula y un símbolo especial',
            'password.min' => 'La contraseña debe tener mínimo 7 caracteres',

            'phone.required' => 'El número de celular es requerido',
            'phone.regex' => 'El número de celular no es válido',
            'phone.min' => 'El número de celular es muy corto',
            'phone.max' => 'El número de celular es muy largo',
        ];
    }
}
