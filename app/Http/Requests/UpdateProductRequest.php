<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class UpdateProductRequest extends FormRequest
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
            'product_name' => 'required | unique:products,product_name,' . $this->id . ',id',
            'product_price' => 'required | integer',
            'product_status' => 'required | boolean',
            'category_id' => 'required | integer'
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
