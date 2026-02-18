<?php

namespace App\Http\Requests\AdminCMS;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'featured' => 'nullable|boolean',
            'category_id' => 'required|exists:categories,id',
            'images' => 'nullable|array',
            'images.*.file' => 'required|file|mimes:jpg,jpeg,png,gif',
            'images.*.alt_text' => 'nullable|string',
            'images.*.title' => 'nullable|string',
            'images.*.sort_order' => 'nullable|integer',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The title is required.',
            'description.string' => 'The description must be a string.',
            'content.string' => 'The content must be a string.',
            'category_id.required' => 'The category is required.',
            'category_id.exists' => 'The selected category is invalid.',
            'images.array' => 'The images must be an array.',
            'images.*.file.required' => 'Each image file is required.',
            'images.*.file.mimes' => 'Each image must be a valid file type (jpg, jpeg, png, gif).',
            'images.*.alt_text.string' => 'The alt text for each image must be a string.',
            'images.*.title.string' => 'The title for each image must be a string.',
            'images.*.sort_order.integer' => 'The sort order must be an integer.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::Validation($validator->errors(), 'Validation Error'));
    }
}
