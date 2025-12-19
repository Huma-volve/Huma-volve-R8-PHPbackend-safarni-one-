<?php

declare(strict_types=1);

namespace App\Http\Requests\Airport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAirportRequest extends FormRequest
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
        $airportId = $this->route('airport');

        return [
            'code' => [
                'sometimes',
                'string',
                'size:3',
                'alpha',
                Rule::unique('airports', 'code')->ignore($airportId),
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'timezone' => ['sometimes', 'string', 'max:50', 'timezone'],
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
            'code.size' => 'Airport code must be exactly 3 characters (IATA code).',
            'code.alpha' => 'Airport code must contain only letters.',
            'code.unique' => 'This airport code already exists.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
            'timezone.timezone' => 'Please provide a valid timezone.',
        ];
    }
}