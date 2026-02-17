<?php

namespace App\Http\Requests\Auth;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    // Check if the user is authorized to make this request
    public function authorize()
    {
        return true;
    }

    // Validation rules for the login request
    public function rules()
    {
        return [
            'email' => 'required|email|exists:users,email', // Check if email exists in the database
            'password' => 'required|min:6', // Password must be at least 6 characters
        ];
    }

    // Custom messages for validation errors
    public function messages()
    {
        return [
            'email.required' => 'The email field is required.',
            'email.email' => 'The email field must be a valid email address.',
            'email.exists' => 'The provided email does not exist in our records.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 6 characters.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::Validation($validator->errors(), 'Validation Error'));
    }
}
