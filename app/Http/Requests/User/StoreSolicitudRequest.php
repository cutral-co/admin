<?php

namespace App\Http\Requests\User;

use App\Http\Requests\TraitRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreSolicitudRequest extends FormRequest
{
    use TraitRequest;

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
            'phone' => ['required', 'string', 'regex:/^\d+$/', 'min:10', 'max:20'],
            'localidad' => 'required|string|in:SI,NO',
            'barrio_id' => 'required_if:localidad,SI|nullable|integer|exists:barrios_municipio,id',
            'provincia_id' => 'required_if:localidad,NO|nullable|integer|exists:provincias,id',
            'municipio' => 'required_if:localidad,NO|nullable|string|max:15',
            'otro_barrio' => 'required_if:localidad,NO|nullable|string',
            'calle' => 'required|string|max:30',
            'altura' => 'required|string|max:15',
            'manzana' => 'nullable|string|max:15',
            'lote' => 'nullable|string|max:15',
            'piso' => 'nullable|string|max:15',
            'depto' => 'nullable|string|max:15',
            'document_front' => 'required|file|image',
            'document_back' => 'required|file|image',
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
            'cuit.regex' => 'El CUIT/CUIL incorporado es invalido',

            'email.required' => 'El correo electronico es requerido',
            'email.regex' => 'Formato de correo incorrecto. Ejemplo: ejemplo@ejemplo.com',
            'email.max' => 'Maximo 255 caracteres',

            'phone.required' => 'El numero de celular es requerido',
            'phone.regex' => 'El numero de celular no es valido',
            'phone.min' => 'El numero de celular es muy corto',
            'phone.max' => 'El numero de celular es muy largo',

            'localidad.required' => 'La localidad es requerida',
            'localidad.in' => 'La localidad es requerida',

            'barrio_id.required_if' => 'Barrio es requerido',
            'barrio_id.exists' => 'Barrio es invalido',

            'provincia_id.required_if' => 'Provincia es requerida',
            'provincia_id.exists' => 'Provincia es invalida',

            'municipio.required_if' => 'Municipio/Localidad es requerido',
            'municipio.max' => 'Municipio no debe superar los 15 caracteres',

            'otro_barrio.required_if' => 'Barrio es requerido',

            'calle.required' => 'Calle es requerido',
            'calle.max' => 'Calle no debe superar los 30 caracteres',

            'altura.required' => 'Altura es requerida',
            'altura.max' => 'Altura no debe superar los 15 caracteres',

            'manzana.max' => 'Manzana no debe superar los 15 caracteres',
            'lote.max' => 'Lote no debe superar los 15 caracteres',
            'piso.max' => 'Piso no debe superar los 15 caracteres',
            'depto.max' => 'Departamento no debe superar los 15 caracteres',

            'document_front.required' => 'Frente documento es requerido',
            'document_front.image' => 'Frente documento debe ser una imagen',

            'document_back.required' => 'Dorso documento es requerido',
            'document_back.image' => 'Dorso documento debe ser una imagen',
        ];
    }
}
