<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Enums\OtpType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResendOtpRequest extends FormRequest
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
            'type' => ['required', 'string', Rule::in(array_column(OtpType::cases(), 'value'))],
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
            'type.in' => 'Type must be either "verification" or "password_reset".',
        ];
    }
}
