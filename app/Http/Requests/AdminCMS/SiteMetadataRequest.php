<?php

namespace App\Http\Requests\AdminCMS;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class SiteMetadataRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'keywords' => 'nullable|string',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png,gif|max:2048', // Image validation for logo
            'favicon' => 'nullable|file|mimes:jpg,jpeg,png,gif,ico|max:2048', // Image validation for favicon
            'logo_alt_text' => 'nullable|string|max:255',
            'logo_title' => 'nullable|string|max:255',
            'favicon_alt_text' => 'nullable|string|max:255',
            'favicon_title' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The site title is required.',
            'logo.file' => 'The logo must be a valid image file.',
            'favicon.file' => 'The favicon must be a valid image file.',
            'logo.mimes' => 'The logo must be a jpg, jpeg, png, or gif image.',
            'favicon.mimes' => 'The favicon must be a jpg, jpeg, png,ico or gif image.',
            'logo.max' => 'The logo image size must not exceed 2MB.',
            'favicon.max' => 'The favicon image size must not exceed 2MB.',
            'logo_alt_text.max' => 'The logo alt text cannot exceed 255 characters.',
            'favicon_alt_text.max' => 'The favicon alt text cannot exceed 255 characters.',
            'logo_title.max' => 'The logo title cannot exceed 255 characters.',
            'favicon_title.max' => 'The favicon title cannot exceed 255 characters.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::Validation($validator->errors(), 'Validation Error'));
    }
}
