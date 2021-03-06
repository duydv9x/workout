<?php

namespace App\Http\Requests;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class BaseRequest
 * @package App\Http\Requests\Api\Eloquent
 */
class BaseRequest extends FormRequest
{
    /**
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        throw new HttpResponseException(response()
            ->json([
                'code' => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
                'message' => __(reset($errors)[0]),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
