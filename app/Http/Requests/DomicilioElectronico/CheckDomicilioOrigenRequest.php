<?php

namespace App\Http\Requests\DomicilioElectronico;

use App\Http\Requests\TraitRequest;
use App\Models\DomicilioElectronico\Log;
use App\Models\DomicilioElectronico\Origin;
use Illuminate\Foundation\Http\FormRequest;

class CheckDomicilioOrigenRequest extends FormRequest
{
    use TraitRequest;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($origin = Origin::where('name', $this->input('origin'))->first()) {
            if ($origin->token === $this->input('token')) {
                return true;
            }
        }
        Log::create([
            'message' => 'No se encuentra autorizado',
            'attributes' => json_encode([
                'origin' => $this->input('origin'),
                'token' => $this->input('token'),
            ])
        ]);
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'documento' => 'required'
        ];
    }
    public function messages()
    {
        return [
            'documento.required' => 'documento es requerido',

        ];
    }
}
