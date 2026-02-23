<?php

namespace App\Http\Requests\AdminCMS;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class AboutSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',

            // ✅ upload file (optional)
            'image' => 'nullable|mimes:jpg,jpeg,png,webp,mp4,avi,mov,mpg,webm,pdf',

            // ✅ update media meta (optional)
            'alt_text' => 'nullable|string|max:255',
            'image_title' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'About section title is required.',
            'title.string' => 'Title must be a string.',
            'title.max' => 'Title must not exceed 255 characters.',

            'description.required' => 'About section description is required.',
            'description.string' => 'Description must be a string.',

            'image.mimes' => 'Image must be jpg, jpeg, png, webp, mp4, avi, mov, mpg, webm or pdf.',
            'image.max' => 'Image size must not exceed 2MB.',

            'alt_text.string' => 'Alt text must be a string.',
            'alt_text.max' => 'Alt text must not exceed 255 characters.',

            'image_title.string' => 'Image title must be a string.',
            'image_title.max' => 'Image title must not exceed 255 characters.',
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
