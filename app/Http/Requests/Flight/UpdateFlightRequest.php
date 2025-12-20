<?php

declare(strict_types=1);

namespace App\Http\Requests\Flight;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFlightRequest extends FormRequest
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
            'flight_number' => ['sometimes', 'string', 'max:10'],
            'airline_id' => ['sometimes', 'integer', 'exists:airlines,id'],
            'aircraft_id' => ['nullable', 'integer', 'exists:aircraft,id'],
            'origin_airport_id' => ['sometimes', 'integer', 'exists:airports,id'],
            'destination_airport_id' => ['sometimes', 'integer', 'exists:airports,id'],
            'departure_time' => ['sometimes', 'date'],
            'arrival_time' => ['sometimes', 'date', 'after:departure_time'],
            'duration_minutes' => ['sometimes', 'integer', 'min:1'],
            'stops' => ['sometimes', 'integer', 'min:0', 'max:5'],
            'layover_details' => ['nullable', 'array'],
            'layover_details.*.airport' => ['required_with:layover_details', 'string'],
            'layover_details.*.duration_minutes' => ['required_with:layover_details', 'integer', 'min:1'],
            'baggage_rules' => ['nullable', 'string', 'max:500'],
            'is_refundable' => ['sometimes', 'boolean'],
            'fare_conditions' => ['nullable', 'string', 'max:1000'],
            'base_price_egp' => ['sometimes', 'integer', 'min:1'],
            'tax_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
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
            'arrival_time.after' => 'Arrival time must be after departure time.',
            'base_price_egp.min' => 'Price must be at least 1 piaster.',
        ];
    }
}