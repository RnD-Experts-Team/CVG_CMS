<?php

namespace App\Http\Requests\AdminCMS;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class FooterRequest extends FormRequest
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
            'contact' => 'required|array',
            'contact.phone' => 'required|string',
            'contact.whatsapp' => 'required|string',
            'contact.email' => 'required|email',
            'contact.address' => 'required|string',

            'social_links' => 'required|array',
            'social_links.*.platform' => 'required|string|in:facebook,instagram,linkedin,whatsapp,fountain,indeed,youtube', // Optional: Adjust as needed
            'social_links.*.url' => 'required|url',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contact.required' => 'The contact information is required.',
            'contact.array' => 'The contact information must be an array.',
            'contact.phone.required' => 'The phone number is required.',
            'contact.phone.string' => 'The phone number must be a valid string.',
            'contact.whatsapp.required' => 'The WhatsApp number is required.',
            'contact.whatsapp.string' => 'The WhatsApp number must be a valid string.',
            'contact.email.required' => 'The email address is required.',
            'contact.email.email' => 'The email address must be a valid email format.',
            'contact.address.required' => 'The address is required.',
            'contact.address.string' => 'The address must be a valid string.',

            'social_links.required' => 'The social links are required.',
            'social_links.array' => 'The social links must be an array.',
            'social_links.*.platform.required' => 'Each social link must have a platform.',
            'social_links.*.platform.string' => 'Each social link platform must be a valid string.',
            'social_links.*.platform.in' => 'Each social link must be one of the following: facebook, instagram, linkedin, whatsapp, fountain, indeed, youtube.',
            'social_links.*.url.required' => 'Each social link must have a URL.',
            'social_links.*.url.url' => 'Each social link URL must be a valid URL.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::Validation($validator->errors(), 'Validation Error'));
    }
}
