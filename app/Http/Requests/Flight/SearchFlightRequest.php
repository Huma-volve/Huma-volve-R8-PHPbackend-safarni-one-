<?php

declare(strict_types=1);

namespace App\Http\Requests\Flight;

use Illuminate\Foundation\Http\FormRequest;

class SearchFlightRequest extends FormRequest
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
            'origin' => ['required', 'string', 'size:3', 'alpha'],
            'destination' => ['required', 'string', 'size:3', 'alpha', 'different:origin'],
            'date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'stops' => ['sometimes', 'integer', 'min:0', 'max:3'],
            'price_min' => ['sometimes', 'numeric', 'min:0'],
            'price_max' => ['sometimes', 'numeric', 'min:0', 'gte:price_min'],
            'airline_id' => ['sometimes', 'integer', 'exists:airlines,id'],
            'departure_time_range' => ['sometimes', 'string', 'in:morning,afternoon,evening,night'],
            'sort_by' => ['sometimes', 'string', 'in:departure_time,base_price_egp,duration_minutes'],
            'sort_order' => ['sometimes', 'string', 'in:asc,desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
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
            'origin.size' => 'Origin must be a 3-letter IATA airport code.',
            'destination.size' => 'Destination must be a 3-letter IATA airport code.',
            'destination.different' => 'Destination must be different from origin.',
            'date.after_or_equal' => 'Date must be today or a future date.',
            'price_max.gte' => 'Maximum price must be greater than or equal to minimum price.',
        ];
    }
}