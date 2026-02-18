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

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|url',
            'media' => 'required|array',
            'media.*.media_id' => 'required|exists:media,id',
            'media.*.sort_order' => 'required|integer',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The title is required.',
            'title.string' => 'The title must be a string.',
            'subtitle.string' => 'The subtitle must be a string.',
            'button_text.string' => 'The button text must be a string.',
            'button_link.url' => 'The button link must be a valid URL.',
            'media.required' => 'The media field is required.',
            'media.array' => 'The media must be an array.',
            'media.*.media_id.required' => 'Each media must have a media ID.',
            'media.*.media_id.exists' => 'Each media ID must exist in the media table.',
            'media.*.sort_order.required' => 'Each media must have a sort order.',
            'media.*.sort_order.integer' => 'The sort order must be an integer.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::Validation($validator->errors(), 'Validation Error'));
    }
}
