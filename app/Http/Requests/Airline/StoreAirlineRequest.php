<?php

declare(strict_types=1);

namespace App\Http\Requests\Airline;

use Illuminate\Foundation\Http\FormRequest;

class StoreAirlineRequest extends FormRequest
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
            'code' => ['required', 'string', 'size:2', 'unique:airlines,code', 'alpha'],
            'name' => ['required', 'string', 'max:255'],
            'logo_url' => ['nullable', 'string', 'url', 'max:500'],
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
            'code.size' => 'Airline code must be exactly 2 characters (IATA code).',
            'code.alpha' => 'Airline code must contain only letters.',
            'code.unique' => 'This airline code already exists.',
            'logo_url.url' => 'Please provide a valid URL for the logo.',
        ];
    }
}