<?php

namespace App\Http\Requests\AdminCMS;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class HeroRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string|max:255',
            'button_text' => 'required|string|max:100',
            'button_link' => 'required|url',
            'media' => 'nullable|array', // Media is optional
            'media.*.file' => 'nullable|mimes:jpg,jpeg,png,webp,mp4,avi,mov,mpg,webm,pdf|max:2048',
            'media.*.alt_text' => 'nullable|string|max:255',
            'media.*.title' => 'nullable|string|max:255',
            'media.*.sort_order' => 'nullable|integer|min:0', // Sort order validation
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title is required for the hero section.',
            'subtitle.required' => 'The subtitle is required for the hero section.',
            'button_text.required' => 'The button text is required.',
            'button_link.required' => 'The button link is required.',
            'button_link.url' => 'The button link must be a valid URL.',
            'media.array' => 'The media field must be an array.',
            'media.*.file.required' => 'Each media file is required.',
            'media.*.file.mimes' => 'Each media file must be a valid file (jpg, jpeg, png, webp, mp4, avi, mov, mpg, webm or pdf).',
            'media.*.file.max' => 'Each media file size must not exceed 2MB.',
            'media.*.alt_text.string' => 'The alt text must be a valid string.',
            'media.*.title.string' => 'The title must be a valid string.',
            'media.*.sort_order.integer' => 'The sort order must be an integer.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::Validation($validator->errors(), 'Validation Error'));
    }
}
