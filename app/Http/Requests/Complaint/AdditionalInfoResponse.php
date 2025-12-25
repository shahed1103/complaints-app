<?php

namespace App\Http\Requests\Complaint;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Http\Responses\response;

class AdditionalInfoResponse extends FormRequest
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
            'problem_description' => 'nullable|string',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf'

        ];
    }

    protected function failedValidation(Validator $validator){

        //Throw a validationexception eith the translated error messages
        $message = "you have sent invalid data";

        throw new ValidationException($validator, Response::Validation([],$message, $validator->errors()));
    }
}
