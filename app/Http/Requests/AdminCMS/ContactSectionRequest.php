<?php

namespace App\Http\Requests\AdminCMS;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ContactSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Contact section title is required.',
            'subtitle.required' => 'Contact section subtitle is required.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            Response::Validation($validator->errors(), 'Validation Error')
        );
    }
}
