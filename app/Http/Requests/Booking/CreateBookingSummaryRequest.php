<?php

declare(strict_types=1);

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingSummaryRequest extends FormRequest
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
            'flight_id' => ['required', 'uuid', 'exists:flights,id'],
            'passengers' => ['required', 'array', 'min:1', 'max:9'],
            'passengers.*.title' => ['required', 'string', 'in:Mr,Mrs,Ms,Dr'],
            'passengers.*.first_name' => ['required', 'string', 'max:100'],
            'passengers.*.last_name' => ['required', 'string', 'max:100'],
            'passengers.*.date_of_birth' => ['required', 'date', 'before:today'],
            'passengers.*.passport_number' => ['required', 'string', 'max:20'],
            'passengers.*.passport_expiry' => ['required', 'date', 'after:today'],
            'passengers.*.nationality' => ['sometimes', 'string', 'size:2'],
            'passengers.*.special_requests' => ['nullable', 'string', 'max:500'],
            'seat_ids' => ['sometimes', 'array'],
            'seat_ids.*' => ['uuid', 'exists:seats,id'],
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
            'flight_id.exists' => 'Selected flight does not exist.',
            'passengers.min' => 'At least one passenger is required.',
            'passengers.max' => 'Maximum 9 passengers allowed per booking.',
            'passengers.*.title.in' => 'Invalid title. Must be Mr, Mrs, Ms, or Dr.',
            'passengers.*.date_of_birth.before' => 'Date of birth must be in the past.',
            'passengers.*.passport_expiry.after' => 'Passport must not be expired.',
            'passengers.*.nationality.size' => 'Nationality must be a 2-letter ISO code.',
            'seat_ids.*.exists' => 'One or more selected seats do not exist.',
        ];
    }
}