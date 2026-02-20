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
            // 🔹 Switch Existing Media (optional)
            'image_media_id' => 'nullable|exists:media,id',

            'image' => 'nullable|mimes:jpg,jpeg,png,webp,mp4,avi,mov,mpg,webm,pdf|max:2048',

            // 🔹 Media Meta
            'alt_text' => 'nullable|string|max:255',
            'image_title' => 'nullable|string|max:255',

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
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not be greater than 255 characters.',

            'image_media_id.exists' => 'The selected image media is invalid.',

            'image.mimes' => 'The image must be a file of type: jpg, jpeg, png, webp, mp4, avi, mov, mpg, webm, pdf.',
            'image.max' => 'The image may not be greater than 2MB.',

            'alt_text.string' => 'The alt text must be a string.',
            'alt_text.max' => 'The alt text may not be greater than 255 characters.',

            'image_title.string' => 'The image title must be a string.',
            'image_title.max' => 'The image title may not be greater than 255 characters.',

            'steps.required' => 'The steps field is required.',
            'steps.array' => 'The steps must be an array.',

            'steps.*.id.exists' => 'The selected process step ID is invalid.',
            'steps.*.title.required' => 'The title of each step is required.',
            'steps.*.title.string' => 'Each step title must be a string.',
            'steps.*.title.max' => 'Each step title may not be greater than 255 characters.',

            'steps.*.description.string' => 'The description of each step must be a string.',

            'steps.*.sort_order.integer' => 'The sort order of each step must be an integer.',
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
