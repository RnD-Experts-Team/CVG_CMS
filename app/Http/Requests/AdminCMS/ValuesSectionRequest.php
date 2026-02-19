<?php

namespace App\Http\Requests\AdminCMS;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ValuesSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'values' => 'required|array',
            'values.*.title' => 'required|string|max:255',
            'values.*.description' => 'nullable|string',
            'values.*.media_id' => 'nullable|exists:media,id',  // Allow existing media ID
            'values.*.image' => 'nullable|file|mimes:jpg,jpeg,png,gif|max:10240', // Max size of 10MB
            'values.*.sort_order' => 'nullable|integer',
            'values.*.alt_text' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title is required.',
            'values.required' => 'The values array is required.',
            'values.*.title.required' => 'Each value must have a title.',
            'values.*.image.file' => 'The image must be a valid file.',
            'values.*.image.mimes' => 'The image must be a file of type: jpg, jpeg, png, gif.',
            'values.*.image.max' => 'The image may not be greater than 10MB.',
            'values.*.alt_text.string' => 'The alt text must be a string.',
            'values.*.alt_text.max' => 'The alt text may not be greater than 255 characters.',
            'values.*.sort_order.integer' => 'The sort order must be an integer.',
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
