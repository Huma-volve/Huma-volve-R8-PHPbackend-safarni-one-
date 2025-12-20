<?php

declare(strict_types=1);

namespace App\Http\Requests\Flight;

use Illuminate\Foundation\Http\FormRequest;

class CompareFlightsRequest extends FormRequest
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
            'flight_ids' => ['required', 'array', 'min:2', 'max:5'],
            'flight_ids.*' => ['required', 'uuid', 'exists:flights,id'],
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
            'flight_ids.required' => 'Please provide flight IDs to compare.',
            'flight_ids.min' => 'You must compare at least 2 flights.',
            'flight_ids.max' => 'You can compare up to 5 flights only.',
            'flight_ids.*.uuid' => 'Invalid flight ID format.',
            'flight_ids.*.exists' => 'One or more flights do not exist.',
        ];
    }
}