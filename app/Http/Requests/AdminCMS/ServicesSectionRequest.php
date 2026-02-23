<?php

namespace App\Http\Requests\AdminCMS;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ServicesSectionRequest extends FormRequest
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
            'button_text' => 'nullable|string|max:255',
            'image' => 'nullable|mimes:jpg,jpeg,png,webp,mp4,avi,mov,mpg,webm,pdf', // Validating the image file type
            'alt_text' => 'nullable|string|max:255',
            'image_title' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The title is required.',
            'title.string' => 'The title must be a valid string.',
            'description.string' => 'The description must be a valid string.',
            'button_text.string' => 'The button text must be a valid string.',
            'image.file' => 'The image must be a valid file.',
            'image.mimes' => 'The image must be a JPG, JPEG, PNG, WEBP, MP4, AVI, MOV, MPG, WEBM or PDF file.',
            'alt_text.string' => 'The alt text must be a valid string.',
            'alt_text.max' => 'The alt text must not exceed 255 characters.',
            'image_title.string' => 'The image title must be a valid string.',
            'image_title.max' => 'The image title must not exceed 255 characters.',
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
