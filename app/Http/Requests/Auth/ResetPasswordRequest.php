<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
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
            'email' => ['required', 'string', 'email'],
            'code' => ['required', 'string', 'size:4', 'regex:/^[0-9]+$/'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(6)
                    ->mixedCase()
                    ->symbols(),
            ],
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.size' => 'Reset code must be 4 digits.',
            'code.regex' => 'Reset code must contain only numbers.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.mixed' => 'Password must contain at least 1 uppercase letter.',
            'password.symbols' => 'Password must contain at least 1 special character.',
        ];
    }
}
