<?php

namespace App\Http\Requests\PublicCMS;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ContactFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'project_details' => 'required|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'full_name.required' => 'The full name is required.',
            'email.required' => 'The email is required.',
            'email.email' => 'The email should be a valid email address.',
            'project_details.required' => 'Project details are required.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::Validation($validator->errors(), 'Validation Error'));
    }
}
