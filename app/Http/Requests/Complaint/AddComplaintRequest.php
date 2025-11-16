<?php

namespace App\Http\Requests\Complaint;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Http\Responses\response;

class AddComplaintRequest extends FormRequest
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
        'complaint_type_id' => 'required|exists:complaint_types,id',
        'complaint_department_id' => 'required|exists:complaint_departments,id',
        // 'complaint_status_id' => 'required|exists:complaint_statuses,id',
        'problem_description' => 'required|string',
        'location' => 'required|string',
        'attachments.*' => 'nullable|mimes:jpg,jpeg,png,pdf|max:2048'
        ];
    }

    protected function failedValidation(Validator $validator){

        //Throw a validationexception eith the translated error messages
        $message = "you have sent invalid data";

        throw new ValidationException($validator, Response::Validation([],$message, $validator->errors()));
    }
}
