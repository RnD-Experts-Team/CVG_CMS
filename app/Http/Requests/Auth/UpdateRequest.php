<?php

namespace App\Http\Requests\Auth;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateRequest extends FormRequest
{
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
        $userId = $this->user()?->id; // authenticated user id

        return [
            'name' => 'sometimes|string|max:255',

            'email' => 'sometimes|required|email|max:255|unique:users,email,'.$userId,

            // only validate if sent; if sent must be >= 6 and match confirmation
            'password' => 'sometimes|nullable|string|min:6|confirmed',

            'password_confirmation' => 'sometimes|nullable|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Name must be a text.',
            'name.max' => 'Name must not be longer than 255 characters.',

            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email must not be longer than 255 characters.',
            'email.unique' => 'This email is already taken.',

            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.string' => 'Password must be a text.',

            'password_confirmation.min' => 'Password confirmation must be at least 6 characters.',
            'password_confirmation.string' => 'Password confirmation must be a text.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::Validation($validator->errors(), 'Validation Error'));
    }
}
