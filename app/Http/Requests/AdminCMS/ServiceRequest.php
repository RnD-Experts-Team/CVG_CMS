<?php

namespace App\Http\Requests\AdminCMS;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_media_id' => 'nullable|exists:media,id',
            'featured' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Service title is required.',
            'title.max' => 'Title may not exceed 255 characters.',
            'image_media_id.exists' => 'Selected image is invalid.',
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
