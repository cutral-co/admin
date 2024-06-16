<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait TraitRequest
{
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(sendResponse(null, $validator->errors(), 422));
    }
}
