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
            'values.*.id' => 'nullable|exists:values_items,id',
            'values.*.title' => 'required|string|max:255',
            'values.*.description' => 'nullable|string',
            'values.*.media_id' => 'nullable|exists:media,id',
            'values.*.sort_order' => 'nullable|integer',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Section title is required.',
            'values.required' => 'Values array is required.',
            'values.*.title.required' => 'Each value must have a title.',
            'values.*.media_id.exists' => 'Selected media is invalid.',
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
