<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reciver' => 'required|string',
            'phone' => 'required|numeric|digits:10',
            'address' => 'required|string',
            'method_payment' => 'required|string',
        ];
    }

    protected function failedValidation(Validator $validator) {
        $response = [
            'status' => 422,
            'error' => 'Validation Error',
            'message' => 'Validation errors occurred',
            'errors' => $validator->errors()
        ];

        throw new HttpResponseException(response()->json($response, 422));
    }
}
