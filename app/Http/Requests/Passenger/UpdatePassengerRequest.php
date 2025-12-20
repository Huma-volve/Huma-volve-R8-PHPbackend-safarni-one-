<?php

declare(strict_types=1);

namespace App\Http\Requests\Passenger;

use App\Enums\PassengerTitle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePassengerRequest extends FormRequest
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
            'title' => ['sometimes', 'string', Rule::in(PassengerTitle::values())],
            'first_name' => ['sometimes', 'string', 'max:100', 'regex:/^[a-zA-Z\s\-]+$/'],
            'last_name' => ['sometimes', 'string', 'max:100', 'regex:/^[a-zA-Z\s\-]+$/'],
            'date_of_birth' => ['sometimes', 'date', 'before:today', 'after:1900-01-01'],
            'passport_number' => ['sometimes', 'string', 'max:20', 'regex:/^[A-Z0-9]+$/'],
            'passport_expiry' => ['sometimes', 'date', 'after:today'],
            'nationality' => ['sometimes', 'string', 'size:2', 'alpha'],
            'special_requests' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.in' => 'Title must be one of: ' . implode(', ', PassengerTitle::values()),
            'first_name.regex' => 'First name can only contain letters, spaces, and hyphens.',
            'last_name.regex' => 'Last name can only contain letters, spaces, and hyphens.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'passport_number.regex' => 'Passport number can only contain uppercase letters and numbers.',
            'passport_expiry.after' => 'Passport must not be expired.',
            'nationality.size' => 'Nationality must be a 2-letter ISO country code.',
        ];
    }
}