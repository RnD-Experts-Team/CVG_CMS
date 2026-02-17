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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'keywords' => 'nullable|string',
            'logo_media_id' => 'nullable|exists:media,id',
            'favicon_media_id' => 'nullable|exists:media,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The site name is required.',
            'name.string' => 'The site name must be a string.',
            'name.max' => 'The site name should not exceed 255 characters.',
            'description.string' => 'The description must be a string.',
            'keywords.string' => 'The keywords must be a string.',
            'logo_media_id.exists' => 'The logo media ID must exist in the media table.',
            'favicon_media_id.exists' => 'The favicon media ID must exist in the media table.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::Validation($validator->errors(), 'Validation Error'));
    }
}
