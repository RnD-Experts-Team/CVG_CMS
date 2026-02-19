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

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'featured' => 'nullable|boolean',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,gif|max:10240', // Max size of 10MB, adjust as needed
            'alt_text' => 'nullable|string|max:255',
            'image_title' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'description.string' => 'The description must be a string.',
            'featured.boolean' => 'The featured field must be a boolean.',
            'image.file' => 'The image must be a valid file.',
            'image.mimes' => 'The image must be a file of type: jpg, jpeg, png, gif.',
            'image.max' => 'The image may not be greater than 10MB.',
            'alt_text.string' => 'The alt text must be a string.',
            'alt_text.max' => 'The alt text may not be greater than 255 characters.',
            'image_title.string' => 'The image title must be a string.',
            'image_title.max' => 'The image title may not be greater than 255 characters.',
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
