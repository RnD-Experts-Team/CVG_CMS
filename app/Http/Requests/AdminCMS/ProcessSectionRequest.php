<?php

namespace App\Http\Requests\AdminCMS;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ProcessSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'image_media_id' => 'nullable|exists:media,id',

            'steps' => 'required|array',
            'steps.*.id' => 'nullable|exists:process_steps,id',
            'steps.*.title' => 'required|string|max:255',
            'steps.*.description' => 'nullable|string',
            'steps.*.sort_order' => 'nullable|integer',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Process section title is required.',
            'steps.required' => 'Steps array is required.',
            'steps.*.title.required' => 'Each step must have a title.',
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
