<?php

namespace App\Http\Requests\DomicilioElectronico;

use App\Http\Requests\TraitRequest;
use Illuminate\Foundation\Http\FormRequest;

class BusquedaPorDniRequest extends FormRequest
{
  use TraitRequest;

  public function rules()
  {
    return [
      'documento' => 'required',
      'genero' => 'nullable|in:m,f,x',
    ];
  }

  public function messages()
  {
    return [
      'documento.required' => 'Documento es requerido',
      'genero.in' => "El genero debe ser 'm', 'f' o 'x'",
    ];
  }
}
